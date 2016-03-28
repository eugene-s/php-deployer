#!/bin/sh

$(config.sh)

cd ..

# Variables
DIRECTORY='current'

CURRENT_BUILD_INDEX=$(cat var/build_index)
CURRENT_MIGRATION_INDEX=$(cat var/migration_index)

((CURRENT_BUILD_INDEX++))

echo $CURRENT_BUILD_INDEX > var/build_index

# Lock current update
LOCK=$(cat var/locked)

if [ $LOCK = 0 ]; then

	echo 1 > var/locked

else
	
	echo 1 > var/repeat_deploy

fi

# If folder not exists
if [ -d "$DIRECTORY" ]; then

	git clone -b $BRANCH $REPO current

fi

cd $DIRECTORY

# Update project
git pull origin $BRANCH
git status

cd ..

NEXT_BUILD_DIRECTORY=builds/$CURRENT_BUILD_INDEX

mkdir $NEXT_BUILD_DIRECTORY
cp -Rf $DIRECTORY/. $NEXT_BUILD_DIRECTORY

cd $NEXT_BUILD_DIRECTORY
rm -rf .git
find -type f -find .gitignore -delete

cd ../..

cp -Ru $NEXT_BUILD_DIRECTORY www

LOCK=0
echo LOCK > var/locked

if [ $(cat var/repeat_deploy) == 1 ]; then
	$(deploy.sh)
fi
