<?php
/**
 * Plugin Name: Gitlab README
 * Plugin URI: https://github.com/mayurgaud/gitlab-files
 * Description: Gitlab README is a plugin that allows you to embed a GitLab README in a page or post using a simple shortcode.
 * Version: 1.0.0
 * Author: Mayur Gaud
 */

require_once 'Michelf/MarkdownExtra.inc.php';
require 'vendor/autoload.php';

use \Michelf\MarkdownExtra;
use League\HTMLToMarkdown\HtmlConverter;

add_shortcode('gitlab_readme', 'gitlab_readme_default');
add_shortcode('gitlab_xml', 'gitlab_xml_default');

// Admin
add_action('admin_menu', 'wpgitlab_plugin_menu');
add_action('admin_init', 'wpgitlab_register_settings');

function wpgitlab_plugin_menu()
{
    add_options_page('WP Gitlab Options', 'WP Gitlab', 'manage_options', 'gitlab-readme', 'wpgitlab_plugin_options');
}

function wpgitlab_register_settings()
{
    //register our settings
    register_setting('gitlab-readme', 'gitlab_url', 'wpgitlab_hosturl');
    register_setting('gitlab-readme', 'gitlab_api_key', 'wpgitlab_validate_api');
}

function wpgitlab_validate_api($input)
{
    return $input;
}

function wpgitlab_hosturl($input)
{
    return $input;
}


function wpgitlab_plugin_options()
{
    include('admin/options.php');
}

/**
 * Handler for gitlab_readme shortcode.
 *
 * @param array $atts
 *
 * @return string
 */
function gitlab_readme_default($atts)
{
    $repo = empty($atts['repo']) ? '' : $atts['repo'];
    $filePath = empty($atts['filepath']) ? '' : $atts['filepath'];
    $branchName = empty($atts['branchname']) ? '' : $atts['branchname'];
    $path = '/projects/' . urlencode($repo) . '/repository/files?file_path=' . $filePath . '&ref=' . $branchName;
    $markdown = gitlab_readme_trim_markdown(apiCall($path));
    $html = MarkdownExtra::defaultTransform($markdown);

    return $html;
}

/**
 * Makes an API call to gitlab and fetches README.md
 *
 * @param $path
 * @return string
 */
function apiCall($path)
{
    $api_url = get_option('gitlab_url', null) . '/api/v3/';
    $api_key = get_option('gitlab_api_key', null);

    $ch = curl_init();
    $headers = array();
    $headers[] = 'PRIVATE-TOKEN: ' . $api_key;
    curl_setopt($ch, CURLOPT_URL, $api_url . $path);
    curl_setopt($ch, CURLOPT_USERAGENT, 'gitlab-readme');
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPGET, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    $response = curl_exec($ch);
    curl_close($ch);

    $json = json_decode($response);
    $markdown = base64_decode($json->content);

    return $markdown;
}

/**
 * Handler for gitlab_xml shortcode.
 *
 * @param array $atts
 *
 * @return string
 */
function gitlab_xml_default($atts)
{
    $repo = empty($atts['repo']) ? '' : $atts['repo'];
    $filePath = empty($atts['filepath']) ? '' : $atts['filepath'];
    $branchName = empty($atts['branchname']) ? '' : $atts['branchname'];

    if ($readMeContent = readMeExists($repo, $branchName)) {
        $markdown = gitlab_readme_trim_markdown($readMeContent);
        $html = MarkdownExtra::defaultTransform($markdown);

        return $html;
    }

    $path = '/projects/' . urlencode($repo) . '/repository/files?file_path=' . $filePath . '&ref=' . $branchName;
    $markdown = apiCall($path);
    $xml = simplexml_load_string($markdown);

    $converter = new HtmlConverter();
    $newReadMe = '<h1>' . $xml->name . ' resource bundle</h1>';
    $newReadMe .= "<br/>";
    $newReadMe .= $xml->description . " <br/>";
    $newReadMe .= "<br/>";

    // Create list of triggers.
    if (array_key_exists('trigger', $xml)) {
        $newReadMe .= "----------";
        $newReadMe .= "<br/>";
        $newReadMe .= '<h2><i>Triggers</i></h2>';
        $newReadMe .= "<br/>";
        foreach ($xml->trigger as $trigger) {
            $newReadMe .= "<br/>";
            $newReadMe .= '<h3>' . $trigger->attributes() . ' microservice</h3>';
            $newReadMe .= "<br/>";

            if ($trigger->description) {
                $newReadMe .= $trigger->description;
            }
            $newReadMe .= "<br/><br/>";

            if (array_key_exists('dataDescriptor', $trigger->event)) {
                $newReadMe .= '<strong>Data Descriptors</strong>';
                $newReadMe .= "<br/><br/>";
                $newReadMe .= "| Name | Description |";
                $newReadMe .= "<br/>";
                $newReadMe .= "| ------------- | :-------------: |";
                $newReadMe .= "<br/>";
                foreach ($trigger->event->dataDescriptor as $dataDescriptor) {
                    $newReadMe .= '| ' . $dataDescriptor->attributes()->name . ' | ' . $dataDescriptor->attributes()->description . ' |';
                    $newReadMe .= "<br/>";
                }
            }
            $newReadMe .= "<br/><br/>";

            if (array_key_exists('mediaDescriptor', $trigger->event)) {
                $newReadMe .= '<strong>Media Descriptors</strong>';
                $newReadMe .= "<br/>";

                $newReadMe .= "<br/>";
                $newReadMe .= "| Name | Description |";
                $newReadMe .= "<br/>";
                $newReadMe .= "| ------------- | :-------------: |";
                $newReadMe .= "<br/>";
                foreach ($trigger->event->mediaDescriptor as $mediaDescriptor) {
                    $newReadMe .= '| ' . $mediaDescriptor->attributes()->name . ' | ' . $mediaDescriptor->attributes()->description . ' |';
                    $newReadMe .= "<br/>";
                }
            }
            $newReadMe .= "<br/><br/>";
        }
    }

    $newReadMe .= "<br/>";

    // Create list of resources.
    if (array_key_exists('resource', $xml)) {
        $newReadMe .= "----------";
        $newReadMe .= "<br/>";
        $newReadMe .= '<h2><i>Resources</i></h2>';
        $newReadMe .= "<br/>";
        foreach ($xml->resource as $resource) {
            $newReadMe .= "<br/>";
            $newReadMe .= '<h3>' . $resource->attributes() . '</h3>';
            $newReadMe .= "<br/>";

            if ($resource->description) {
                $newReadMe .= $resource->description;
            }

            $newReadMe .= "<br/><br/>";

            if ($resource->input) {
                if (array_key_exists('parameter', $resource->input)) {
                    $newReadMe .= '<strong>Input Parameters</strong>';
                    $newReadMe .= "<br/><br/>";
                    $newReadMe .= "| Name | Description |";
                    $newReadMe .= "<br/>";
                    $newReadMe .= "| ------------- | :-------------: |";
                    $newReadMe .= "<br/>";
                    foreach ($resource->input->parameter as $parameters) {
                        $newReadMe .= '| ' . $parameters->attributes()->name . ' | ' . $parameters->attributes()->description . ' |';
                        $newReadMe .= "<br/>";
                    }
                }
                $newReadMe .= "<br/><br/>";
            }

            if ($resource->route) {
                $newReadMe .= '<strong>Route</strong>';
                $newReadMe .= "<br/><br/>";
                foreach ($resource->route as $routes) {
                    $newReadMe .= '```';
                    $newReadMe .= "<br/>";
                    $newReadMe .= 'route code = ' . $routes->attributes()->code;
                    $newReadMe .= "<br/>";

                    if ($routes->description) {
                        $newReadMe .= "description = $routes->description";
                        $newReadMe .= "<br/>";
                    }

                    if ($routes->dataDescriptor) {
                        $newReadMe .= 'dataDescriptor = ' . $routes->dataDescriptor->attributes()->name;
                        $newReadMe .= "<br/>";
                    }

                    if ($routes->mediaDescriptor) {
                        $newReadMe .= "mediaDescriptor = $routes->mediaDescriptor";
                        $newReadMe .= "<br/>";
                    }

                    $newReadMe .= "<br/>";
                    $newReadMe .= "```";
                    $newReadMe .= "<br/>";
                }
            }
        }
    }

    $markdown = $converter->convert($newReadMe);
    $api_url = get_option('gitlab_url', null) . '/api/v3/';
    $api_key = get_option('gitlab_api_key', null);

    $commitMessage = urlencode('Readme Updated');
    $content = base64_encode($markdown);

    $newPath = '/projects/' . $repo . '/repository/files?file_path=README.md&branch_name=' . $branchName . '&encoding=base64&content=' . $content . '&commit_message=' . $commitMessage;

    $curl = curl_init();

    curl_setopt_array($curl, array(
        CURLOPT_URL => $api_url . $newPath,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => "",
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 30,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => "PUT",
        CURLOPT_SSL_VERIFYHOST => false,
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_HTTPHEADER => array(
            "cache-control: no-cache",
            "private-token: $api_key"
        ),
    ));

    $response = curl_exec($curl);
    $err = curl_error($curl);

    curl_close($curl);

    if ($err) {
        return "cURL Error #:" . $err;
    } else {
        return $response;
    }
}

/**
 * Check if the README file is present or not in the
 * repository.
 *
 * @param $repo
 * @param $branchName
 * @return bool
 */
function readMeExists($repo, $branchName)
{
    $path = '/projects/' . urlencode($repo) . '/repository/files?file_path=/test/README.md&ref=' . $branchName;
    $markdown = apiCall($path);

    return $markdown;
}

/**
 * Trim lines from beginning of markdown text.
 *
 * @param string $markdown
 * @param integer $lines Optional number of lines to trim from beginning of supplied markdown.
 *
 * @return string
 */
function gitlab_readme_trim_markdown($markdown, $lines = 0)
{
    if (0 < $lines) {
        $markdown = implode("\n", array_slice(explode("\n", $markdown), $lines));

        return $markdown;
    }

    return $markdown;
}