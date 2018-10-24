<?php

namespace FPayments;

if (!function_exists('mb_str_split')) {
    function mb_str_split($string, $split_length = 1, $encoding = null) {
        if (is_null($encoding)) {
            $encoding = mb_internal_encoding();
        }

        if ($split_length < 1) {
            return false;
        }

        $return_value = array();
        $string_length  = mb_strlen($string, $encoding);
        for ($i = 0; $i < $string_length; $i += $split_length)
        {
            $return_value[] = mb_substr($string, $i, $split_length, $encoding);
        }
        return $return_value;
    }
}

if (!function_exists('stripslashes_gpc')) {
    function stripslashes_gpc(&$value) {
        $value = stripslashes($value);
    }
}

require_once "fpayments_config.php";


class FormError extends \Exception {}


class PaymentForm {
    private $merchant_id;
    private $secret_key;
    private $is_test;
    private $plugininfo;
    private $cmsinfo;
    private $callback_on_failure;

    function __construct(
        $merchant_id,
        $secret_key,
        $is_test,
        $plugininfo = '',
        $cmsinfo = ''
    ) {
        $this->merchant_id = $merchant_id;
        $this->secret_key = $secret_key;
        $this->is_test = (bool) $is_test;
        $this->plugininfo = $plugininfo ?: 'FPayments/PHP v.' . phpversion();
        $this->cmsinfo = $cmsinfo;
        $this->callback_on_failure = false;
    }

    public static function abs($path) {
        return ModuleConfig::HOST . $path;
    }

    function get_url() {
        return self::abs('/pay/');
    }

    function get_transaction_info_url() {
        return self::abs('/api/v1/transaction/');
    }

    function get_rebill_url() {
        return self::abs('/api/v1/rebill/');
    }

    function enable_callback_on_failure() {
        $this->callback_on_failure = true;
        return $this;
    }

    function disable_callback_on_failure() {
        $this->callback_on_failure = true;
        return $this;
    }

    function compose(
        $amount,
        $currency,
        $order_id,
        $client_email,
        $client_name,
        $client_phone,
        $success_url,
        $fail_url,
        $cancel_url,
        $callback_url,
        $meta = '',
        $description = '',
        $receipt_contact = '',
        array $receipt_items = null,
        $recurring_frequency = '',
        $recurring_finish_date = ''
    ) {
        if (!$description) {
            $description = "Заказ №$order_id";
        }
        $form = array(
            'testing'               => (int) $this->is_test,
            'merchant'              => $this->merchant_id,
            'unix_timestamp'        => time(),
            'salt'                  => $this->get_salt(32),
            'amount'                => $amount,
            'currency'              => $currency,
            'description'           => $description,
            'order_id'              => $order_id,
            'client_email'          => $client_email,
            'client_name'           => $client_name,
            'client_phone'          => $client_phone,
            'success_url'           => $success_url,
            'fail_url'              => $fail_url,
            'cancel_url'            => $cancel_url,
            'callback_url'          => $callback_url,
            'callback_on_failure'   => $this->callback_on_failure,
            'meta'                  => $meta,
            'sysinfo'               => $this->get_sysinfo(),
            'recurring_frequency'   => $recurring_frequency,
            'recurring_finish_date' => $recurring_finish_date,
        );
        if ($receipt_items) {
            if (!$receipt_contact) {
                throw new FormError('receipt_contact required');
            }
            $items_sum = 0;
            $items_arr = array();
            foreach ($receipt_items as $item) {
                $items_sum += $item->get_sum();
                $items_arr[] = $item->as_dict();
            }
            $items_sum = round($items_sum, 2);
            if ($items_sum != $amount) {
                throw new FormError("Amounts mismatch: sum of cart items: ${items_sum}, order amount: ${amount}");
            }
            $form['receipt_contact'] = $receipt_contact;
            $form['receipt_items'] = json_encode($items_arr);
        };
        $form['signature'] = $this->get_signature($form);
        return $form;
    }

    private function get_sysinfo() {
        return json_encode(array(
            'language' => 'PHP ' . phpversion(),
            'plugin' => $this->plugininfo,
            'cms' => $this->cmsinfo,
        ));
    }

    function is_signature_correct(array $form) {
        if (!array_key_exists('signature', $form)) {
            return false;
        }
        return $this->get_signature($form) == $form['signature'];
    }

    function is_order_completed(array $form) {
        $is_testing_transaction = ($form['testing'] === '1');
        return ($form['state'] == 'COMPLETE') && ($is_testing_transaction == $this->is_test);
    }

    public static function array_to_hidden_fields(array $form) {
        $result = '';
        foreach ($form as $k => $v) {
            $result .= '<input name="' . htmlspecialchars($k) . '" value="' . htmlspecialchars($v) . '" type="hidden">';
        }
        return $result;
    }

    function get_signature(array $params, $key = 'signature') {
        $keys = array_keys($params);
        sort($keys);
        $chunks = array();
        foreach ($keys as $k) {
            $v = (string) $params[$k];
            if (($v !== '') && ($k != 'signature')) {
                $chunks[] = $k . '=' . base64_encode($v);
            }
        }
        return $this->double_sha1(implode('&', $chunks));
    }

    private function double_sha1($data) {
        for ($i = 0; $i < 2; $i++) {
            $data = sha1($this->secret_key . $data);
        }
        return $data;
    }

    private function get_salt($length = 10) {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $result = '';
        for ($i = 0; $i < $length; $i++) {
            $result .= $characters[rand(0, strlen($characters) - 1)];
        }
        return $result;
    }

    function rebill(
        $amount,
        $currency,
        $order_id,
        $recurrind_tx_id,
        $recurring_token,
        $description = ''
    ){
        if (!$description) {
            $description = "Заказ №$order_id";
        }
        $form = array(
            'testing'               => (int) $this->is_test,
            'merchant'              => $this->merchant_id,
            'unix_timestamp'        => time(),
            'salt'                  => $this->get_salt(32),
            'amount'                => $amount,
            'currency'              => $currency,
            'description'           => $description,
            'order_id'              => $order_id,
            'initial_transaction'   => $recurrind_tx_id,
            'recurring_token'       => $recurring_token,
        );
        $form['signature'] = $this->get_signature($form);
        $paramstr = http_build_query($form);
        $ch = curl_init($this->get_rebill_url());
        curl_setopt($ch, CURLOPT_USERAGENT, $this->plugininfo);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $paramstr);
        $result = curl_exec($ch);
        curl_close($ch);
        return json_decode($result);
    }

    function get_transaction_info($transaction_id) {
        $form = array(
            'transaction_id'        => $transaction_id,
            'merchant'              => $this->merchant_id,
            'unix_timestamp'        => time(),
            'salt'                  => $this->get_salt(32),
        );
        $form['signature'] = $this->get_signature($form);
        $paramstr = http_build_query($form);
        $ch = curl_init($this->get_transaction_info_url() . '?' . $paramstr);
        curl_setopt($ch, CURLOPT_USERAGENT, $this->plugininfo);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $result = curl_exec($ch);

        if (curl_error($ch)) {
            error_log("Error while requesting transaction info: ".curl_error($ch));
            return;
        }
        curl_close($ch);

        $data = json_decode($result, true);

        if ($data['status'] != 'ok') {
            return;
        }
        return $data['transaction'];
    }
}


abstract class AbstractCallbackHandler {
    /**
    * @return PaymentForm
    */
    abstract protected function get_fpayments_form();
    abstract protected function load_order($order_id);
    abstract protected function get_order_currency($order);
    abstract protected function get_order_amount($order);
    /**
    * @return bool
    */
    abstract protected function is_order_completed($order);
    /**
    * @return bool
    */
    abstract protected function mark_order_as_completed($order, array $data);
    /**
    * @return bool
    */
    abstract protected function mark_order_as_error($order, array $data);

    function show(array $data) {        
        if (get_magic_quotes_gpc()) {
           array_walk_recursive($data, 'stripslashes_gpc');
        }
        $error = null;
        $debug_messages = array();
        $ff = $this->get_fpayments_form();

        if (!$ff->is_signature_correct($data)) {
            $error = 'Incorrect "signature"';
        } else if (!($order_id = (int) $data['order_id'])) {
            $error = 'Empty "order_id"';
        } else if (!($order = $this->load_order($order_id))) {
            $error = 'Unknown order_id';
        } else if ($this->get_order_currency($order) != $data['currency']) {
            $error = 'Currency mismatch: "' . $this->get_order_currency($order) . '" != "' . $data['currency'] . '"';
        } else if ($this->get_order_amount($order) != $data['amount']) {
            $error = 'Amount mismatch: "' . $this->get_order_amount($order) . '" != "' . $data['amount'] . '"';
        } else if ($ff->is_order_completed($data)) {
            $debug_messages[] = "info: order completed";
            if ($this->is_order_completed($order)) {
                $debug_messages[] = "order already marked as completed";
            } else if ($this->mark_order_as_completed($order, $data)) {
                $debug_messages[] = "mark order as completed";
            } else {
                $error = "Can't mark order as completed";
            }
        } else {
            $debug_messages[] = "info: order not completed";
            if (!$this->is_order_completed($order)) {
                if ($this->mark_order_as_error($order, $data)) {
                    $debug_messages[] = "order status changed to 'failed'";
                } else {
                    $error = "Can't change order status to 'failed'";
                }
            }
        }

        if ($error) {
            echo "ERROR: $error\n";
        } else {
            echo "OK $order_id\n";
        }
        foreach ($debug_messages as $msg) {
            echo "...$msg\n";
        }
    }
}


class ReceiptItem {
    const NO_VAT  = 'none';   # без НДС
    const VAT_0   = 'vat0';    # НДС по ставке 0%
    const VAT_10  = 'vat10';   # НДС чека по ставке 10%
    const VAT_18  = 'vat18';   # НДС чека по ставке 18%
    const VAT_20  = 'vat20';   # НДС чека по ставке 18%
    const VAT_110 = 'vat110';  # НДС чека по расчетной ставке 10/110
    const VAT_118 = 'vat118';  # НДС чека по расчетной ставке 18/118

    private $title;
    private $price;
    private $n;
    private $nds;

    function __construct($title, $price, $n = 1, $nds = null) {
        $this->title = $title;
        $this->price = $price;
        $this->n = $n;
        $this->nds = $nds ? $nds : self::NO_VAT;
    }

    function as_dict() {
        return array(
            'quantity' => $this->n,
            'price' => $this->price,
            'vat' => $this->nds,
            'name' => $this->title,
        );
    }

    function get_sum() {
        $result = $this->n * $this->price;
        return $result;
    }

    static function guess_vat($rate) {
        if ($rate == 0) {
            return 'vat0';
        } else if ($rate == 10) {
            return 'vat10';
        } else if ($rate == 18) {
            return 'vat18';
        } else if ($rate == 20) {
            return 'vat20';  // I can see the future
        }
    }
}
