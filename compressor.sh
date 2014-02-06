#!/bin/sh
for file in `find ./web/bundles/adentifycommon/app/ -name "*.js"`
do
echo "Compressing $file …"
java -jar ./app/Resources/java/yuicompressor.jar --type js -o $file $file
done
