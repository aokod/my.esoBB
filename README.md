### myesoBB
myesoBB (myeso.org) is a free host for esoBB forums.

The myesoBB website uses a heavily modified version of esoBB (of course) which takes the forum installer and uses it to create multiple forum instances.

In order to create your own forum host using this code, you must have the following:
1. A template forum (`/var/www/myeso_template`) which contains an unmodified instance of the forum software.  The purpose of this is to keep all of the core files, including plugins and skins, in one location so they can be managed in order to easily upgrade or modify the software across all instances.  Symbolic links are used to tie the files into each individual forum instance.  The template forum also contains the default config which determines the majority of config values for every forum.
2. A MySQL user (`myeso_createdb`) with permissions to create users and databases.  For security reasons, every forum has its own database and user that is permitted to modify that database only.
3. nginx web server and Certbot (Let's Encrypt) for SSL certificates.  The installer generates an nginx configuration for each instance and issues certificates for the subdomain on which the forum instance lives (e.g: kravmaga.myeso.org).

The template forum software makes some slight modifications to the esoBB forum software in order to accommodate the way that it is hosted.  First, the default configuration includes values (such as database host) that are shared across forums.  Plugin/skin/language pack uploading is disabled for security reasons.  Instead, every forum uses the same directory (hence the template forum) to supply the same plugins, skins and language packs.

#### To-do list
 - [ ] Finish logic
 - [ ] Code cleanup (remove several redundancies in the installer)
 - [ ] SMTP configuration; forum admins should be able to use our mailserver (do_not_reply@myeso.org) or an external one using SMTP.
 - [ ] A plugin for all forums that gives forum admins the ability to delete their forum.
