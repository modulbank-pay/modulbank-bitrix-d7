#!/bin/bash

set -e

IS_INITIAL=''
FROM_COMMIT=''
VERSION=''

usage() {
    echo 'usage';
    cat <<-END
	$0 [-i] [-f <from_commit>] [-v <version>]
	
	Builds a release file for bitrix marketplace.
	
	Options:
	  -i - build 'initial' release which contains all files (not an incremental one)
	  -f <from_commit> - build incremental release with changes since <from_commit>. By default the last tag since current commit.
	  -v <version> - set version of the release (by default current tag is used)
END
    exit;
}

while getopts "if:v:h" opt; do
    case "$opt" in 
        i)
            IS_INITIAL=1
            ;;
        f)
            FROM_COMMIT=$OPTARG
            ;;
        v)
            VERSION=$OPTARG
            ;;
        h)
            usage
            ;;
    esac
done
shift $((OPTIND-1))

if [[ -z $FROM_COMMIT ]]; then
    # Get the previouse release tag
    FROM_COMMIT=$(git describe --tags HEAD^)

    if [[ -z $FROM_COMMIT ]]; then
        echo "Can't read the previous release version"
        exit 1;
    fi
fi

if [[ -z $VERSION ]]; then
    VERSION=$(git describe --tags)

    if [[ -z $VERSION ]]; then
        echo "Can't get a version (tag) for the current release"
        exit 1;
    fi
fi

if [[ $VERSION == $FROM_COMMIT ]]; then
    echo "The current version is the same as the previous one."
    echo "Make sure you've tagged the current release or use '--version' argument"
    exit 1
fi

cat <<END
Going to create a release with the following options:
  version: $VERSION
  initial: $(test -n "$IS_INITIAL" && echo 'yes' || echo 'no')
  from version: $FROM_COMMIT
END

rm -rf build && mkdir build

if [[ -n $IS_INITIAL ]]; then
    VERSION_DIR=".last_version"
    FILE_LIST_COMMAND='find install -type f'
else
    VERSION_DIR=$VERSION
    FILE_LIST_COMMAND="git diff --name-only $FROM_COMMIT HEAD"
fi

WORKDIR="build/$VERSION_DIR"

# create directories for files that were been changed
$FILE_LIST_COMMAND | while read f; do
    directory=$(dirname $f)
    mkdir -p "$WORKDIR/$directory"
done

# Install the php files with encoding convertion
$FILE_LIST_COMMAND | grep "\.php$" | xargs -n1 -I@ sh -c "cat @ | iconv -t cp1251 > '$WORKDIR/@'"

# Install the rest of files without conversion
$FILE_LIST_COMMAND | grep -v "\.php$" | xargs -n1 -I@ sh -c "cat @ > '$WORKDIR/@'"

if $FILE_LIST_COMMAND | grep install.php; then
    cp include.php $WORKDIR/
fi

echo
echo "Release contents:"
(cd build && tar czvf $VERSION_DIR.tgz $VERSION_DIR)
echo "-- end of the list --"

cat <<END
Done!

Check out file build/$VERSION_DIR.tgz
END
