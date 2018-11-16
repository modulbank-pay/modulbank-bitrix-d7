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
    VERSION=$(git describe --exact-match --tags HEAD 2>/dev/null ||:)

    if [[ -z $VERSION ]]; then
        echo "Can't get a version (tag) for the current release. Make sure you tagged the current revision."
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

rm -rf build

if [[ -n $IS_INITIAL ]]; then
    VERSION_DIR=".last_version"
else
    VERSION_DIR=$VERSION
fi

get_release_contents() {
    if [[ -n $IS_INITIAL ]]; then
        find install -type f
    else
        git diff --name-only $FROM_COMMIT HEAD | grep -E '^(install/|include.php)'
    fi
}

WORKDIR="build/$VERSION_DIR"

mkdir -p "$WORKDIR"

# create directories for files that were been changed
get_release_contents | while read f; do
    directory=$(dirname $f)
    mkdir -p "$WORKDIR/$directory"
done

# Install the php files with encoding convertion
get_release_contents | grep "\.php$" | xargs -n1 -I@ sh -c "(test -e @ && cat @ || echo) > '$WORKDIR/@'"

# Install the rest of files without conversion
get_release_contents | grep -v "\.php$" | xargs -n1 -I@ sh -c "(test -e @ && cat @ || echo) > '$WORKDIR/@'"

if get_release_contents | grep include.php; then
    cp include.php $WORKDIR/
fi

# Set release version
cat > $WORKDIR/version.php <<END
<?
\$arModuleVersion = array(
    "VERSION" => "$VERSION",
    "VERSION_DATE" => "$(date +'%Y-%m-%d %H:%M:%S')"
);
END

echo
echo "Release contents:"
(cd build && tar czvf $VERSION_DIR.tgz $VERSION_DIR)
echo "-- end of the list --"

cat <<END
Done!

Check out file build/$VERSION_DIR.tgz
END
