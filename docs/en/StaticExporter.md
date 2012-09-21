# Static Exporter

## Introduction

StaticExporter allows you to export a static copy of your website either as a
tar.gz archive or to a separate folder. It does this by saving every page and
other registered URL to the file system. You can then server the exported 
website on your production server or use it as a back up system.

## Requirements

 - Unix filesystem
 - Tar installed

<div class="warning" markdown='1'>
	This has not been tested on Windows
</div>

## Usage

There are three ways the StaticExporter can be invoked depending on your use 
case. 

### GUI

If you're logged into your site as an administrator or your website is in 
development mode, you can access the GUI for generating the export at:
http://yoursite.com/StaticExporter/. The GUI allows you to select a few
configuration options then will generate a tar.gz archive of the website.

### StaticExporterTask

Accessing http://yoursite.com/dev/tasks/StaticExporterTask will generate the
export of the website and save it to a folder on your filesystem. Unlike the
GUI option this does not allow you to configure options in the browser, instead
it relies on the developer setting the options via statics or through GET
parameters.

### Sake

To generate the export via command line ([sake](/framework/en/topics/commandline.md))

	sake dev/tasks/StaticExporterTask
	
## Options

Both the StaticExporterTask and Sake task take the following GET params. The
GUI method only allows you to customize the baseurl path.

* baseurl - (string) Base URL for the published site
* symlink - (false|true) Copy the assets directory into the export or, simply 
symlink to the original assets folder. If you're deploying to a separate 
server ensure this is set to false.
* quiet - (false|true) Output progress of the export.
* path - (string) server path to generate export to. 

Note that the path directory will be populated with the new exported files. 
This script does not empty the directory first so you may which to remove the
folder (`rm -rf cache && sake dev/tasks/StaticExporterTask path=cache`)
