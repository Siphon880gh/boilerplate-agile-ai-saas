echo "PWD is the path where you executed the .sh file from: $(pwd)"
echo ""
echo "File: $0"
echo ""
echo "Dir of .sh file relative to the path where you executed the .sh file: $(dirname $0)"
echo ""
echo "Realpath of file into file path isn't supported by all Unix-like systems, so here is the full implementation. Here is the absolute path of the .sh file regardless where you executed the .sh file from: " $(cd "$(dirname "$0")" && pwd -P)/$(basename "$0")
echo ""
echo "Realpath of file into directory path isn't supported by all Unix-like systems, so here is the full implementation. Here is the absolute path of the .sh file regardless where you executed the .sh file from: " $(cd "$(dirname "$0")" && pwd -P)/
