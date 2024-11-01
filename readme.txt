=== WP Favicons plug-in ===
Contributors: cogmios
Donate link: http://edward.de.leau.net
Tags: google, favicon, links, post, sidebar, images
Requires at least: 3.3
Tested up to: 3.3
Stable tag: 0.6.6

BETA - Show Favicons in front of links in content, comments, widgets; scan pages, roots, google s2, geticons for favicons;
apply image filters to favicons;  


== Description ==

This plugin puts a recognizable icon before each link; it makes your website lively and 
recognizable: Compare listings of dull links to listings with an icon before it: users will 
instantly recognize the familiar icons to your benefit. Compare this with the desktop on your 
computer: 100 links in plain text or 100 links with app icons, what do you prefer? Exactly!

* Note 1: this plugin only supports PHP version 5.3 or later (see FAQ)
* Note 2: this plugin is still in BETA until it is at version 1.0

Features:

* includes a server component and a client component, so you can have a single icon server on 
  a high power machine and lots of clients just requesting icons, you could even set up a server
  for your friends (or as a business functionality). However ofcourse you can enable both the client
  and the server if your run this on just 1 installation, 
  it also means you can build clients in different languages requesting icons from your server. 
* add icons in content, widget areas, comment area and comment author link each with its own styling.
  If you understand filters in WordPress you can add them to any piece of content.
* add icons from scanning sites directly, their /favicon.ico, Google and getIcon.org. The more
  providers you add the higher the chance it will get to 100% icons. (however there are some urls
  using conditional javascript redirects to other sites containing the favicons that still slip).
* image filtes such as converting all icons to PNG. If you understand WordPress filters you can
  add more icon filters to build a nice physical directory with icons in your desired format
* add default icons (if a site has none), several IDENTICONS is included, you can add more
  default icons if you know WordPress coding a bit.
* exclusion of filetypes: do not process pdf, zip etc...
* status icons showing you: good links, redirects and bad links (handy for cleaning). You can
  extend this to all HTTP return codes. 418 however is included if you can find a site that
  returns a 418 :)
* lots of developer filters so you can after process image or add your own components in the flow

Low Fat:

The server will search for an icon only every 30 seconds (default) and in the meanwhile use Google
for icons so you will SLOWLY build a good icon base. On top of that the client requests are
transient cached on 1 hour (default) so every unique request only call the server once each
hour (and even then only if a visitor hits that page). On top of that the server has a transient
cache also on 1 hour (default). So ... it will build up very slowly with
performance and being nice to your database and providers in mind. However notice that if you have
a lot of outgoing links the server will grow to a large database (it stores all requests and
icons). 

Developers:

* can add their own plugins for each of the modules provided:
* can add their own plugins to define pieces of content to filter
* can add plugins to the cache
* can add plugins for other favicon sources or repositories
* can add plugins for providing other default icons
* can add plugins for handling other filetypes content

The plugin uses a plugin framework (GPLv2) so you can also copy the framework and make your
own plugin around it. If you have improvements on it please send them to me.

Languages:

The plugin is ready for internationalization but since I still intent to make a lot 
of changes full translation will happen in a later release. I also need to write
some of the inline help.


== Changelog ==

* v.0.1 initial version
* v.0.2 adds widget support
* v.0.3 cache support + file types exclusions + static css
* v.0.4.0 I18n, added plugins support, new plugin framework
* v.0.4.5 app.facebook.com icons are now supported
* v.0.4.7 comment author link added and fixes for icons with different extensions
* v.0.4.8 temporary disabled the cache
* v.0.4.9 new db hash fix 
* v.0.5.4 adds status icons
* v.0.5.7 quick fix for wp3.3
* v.0.6.0 code now split in server and client component
* v.0.6.3 added more transient xmlrpc caching + optimzed outgoing requests timeout
* v.0.6.6 changed database check to check updates in informationschema

== Installation ==

1. download the plugin from http://wordpress.org/extend/plugins/wp-favicons/
1. copy the complete directory in your plugin dir
1. activate the CLIENT plugin in WordPress clients 
1. activate the SERVER plugin on the WP install you are going to use as icon server
1. (or activate them both if you have just 1 installation)
1. go to setting > icon client / icon server to configure the settings

== Screenshots ==

1. Example of my sidebar "lifeline" (accidently these are all coming from my delicious account) see what differences it makes!
2. Example of my site: http://populair.eu : 4.5 million users really liked the layout with the icons to be much more useable (!)

== Contact Info ==

contact info:
http://edward.de.leau.net/contact

== FAQ ==

Q: Is there an example?
A: See "http://populair.eu" that uses the client to request icons from the server

Q: What is Dummy Mode?
A: If you have not turned on the cache you are in dummy modus: all your icons will be 
   retrieved from Google.

Q: Should I turn on all image providers?
A: This will take the longest time during load but it definitely will provide you with
   the most correct list of icons e.g. facebook application need to have a pagescan for the 
   favicon
   
Q: Does it work with all favicons out there?
A: Still enhancing the code to at least grab everything possible

Q: Why dont i see all icons?
A: There are several caches to slowly load the icons from requests. If you want to disable
   caching and BURST a lot of requests to both providers and your own database you can set
   WP_FAVICON_TRANSIENT_TIMEOUT to 1 in the client and WP_FAVICON_REQUEST_TIMEOUT in the 
   server files (wp-favicons-client.php and wp-favicons-server.php). Only recommended if you
   have the bandwidth
   
Q: When should I clean the cache?
A: After you make changes to the settings or if a new version of the plugin arrives that fixes
   more items that concern the cache
   
Q: Is it final yet?
A: No only if it reaches version 1.0.0 it will be out of beta so if you have suggestions for improvements
   please e-mail me
   
Q: What are the PHP 5.3 or later requirements?
A: these are my includes: –with-curl –with-pdo-mysql=mysqlnd –with-zlib –with-mcrypt 
   –enable-mbstring –enable-mbregex –with-gd –with-jpeg-dir –with-png-dir –with-xmlrpc    

Q: If i build my own client what is the XMLRPC call?
A: default WP XMLRPC: blogid, username, password, followed by 'text', you will get the 
   text as return value enriched with links to icons   
   
   
   
   
   



