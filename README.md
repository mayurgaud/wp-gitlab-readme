GitLab Readme Wordpress Plugin
===================


Gitlab README is a plugin that allows you to embed markdown from Gitlab in a page or post using a simple shortcode.

----------


Prerequisites
-------------
Composer install one of the package that we need for converting HTML to Markdown format (https://github.com/thephpleague/html-to-markdown).

Steps for composer installation

```
php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');"
php -r "if (hash_file('SHA384', 'composer-setup.php') === 'e115a8dc7871f15d853148a7fbac7da27d6c0030b848d9b3dc09e2a0388afed865e6a3d6b3c0fad45c48e2b5fc1196ae') { echo 'Installer verified'; } else { echo 'Installer corrupt'; unlink('composer-setup.php'); } echo PHP_EOL;"
php composer-setup.php
mv composer.phar /usr/local/bin/composer
php -r "unlink('composer-setup.php');"

```


Installation
-------------

 1. Upload gitlab-readme plugin in admin panel of wordpress instance.
 2. Then inside the plugin directory wp-content/plugins/gitlab-readme/ run `composer install`.
 3. Activate the plugin through the 'Plugins' menu in WordPress.
 4. Add your gitlab url and api key in the settings of plugin, which is under Settings > WP Gitlab
 5. Add the desired shortcode in your posts and pages.

----------


Usage
-------------------

**gitlab_readme**

This shortcode embeds the project's readme.

>[gitlab_readme repo="microservices/example-php" filepath="README.md" branchname="master"]

In the above shortcode,
 
1. repo attribute is the name of the gitlab repository. 
2. filepath attribute is the name with of the file that we want to display in our post or page of wordpress. 
3. branchname is the name of the branch from which we want to pull the file content.

We just have to add this shortcode in the post or page and voila!!

**gitlab_xml**

This shortcode adds the content in README.md(from resource.xml) if it is empty and then displays that README.md of that repo. 

>[gitlab_xml repo="microservices/mail" filepath="/src/main/resources/RESOURCEINF/resource.xml" branchname="master"]

In the above shortcode,
 
1. repo attribute is the name of the gitlab repository. 
2. filepath attribute is the name with of the file that we want to display in our post or page of wordpress. 
3. branchname is the name of the branch from which we want to pull the file content.

For this shortcode to work properly we need an empty README.md file in root directory of the project.