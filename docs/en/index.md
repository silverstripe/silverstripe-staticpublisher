# StaticPublisher

StaticPublisher is a module providing an extension to SilverStripe to allow 
developers to generate static exports of their SilverStripe sites either 
for performance or as a backup system.

There are two extensions provided by the module:

## Static Publisher

Publish selected SilverStripe pages as HTML / PHP files to get a performance 
increase on those pages. You can run the static content alongside a full 
installation to allow for seamless integration. Also supports syncing published
content to multiple servers for load balancing.

* [Static Publisher](StaticPublisher.md)

## Static Exporter

Export your entire website to HTML pages. Suitable if your entire site is 
static and you wish to deploy the content to a machine other than the CMS or
as a backup measure to your website

* [Static Exporter](StaticExporter.md)
