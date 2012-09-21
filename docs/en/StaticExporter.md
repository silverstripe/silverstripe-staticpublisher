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
it relies on the developer setting the options via statics (as deploy)

### Sake

To generate the export via command line ([sake](framework/en/topics/commandline.md))

	sake dev/tasks/StaticExporterTask
	
## Options