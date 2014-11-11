UPDATE glpi_configs set language = 'I18NTOCHANGE' where id = 1;
INSERT INTO `glpi_authldaps` VALUES ('','yunohost','localhost','dc=yunohost,dc=org','',389,'(objectClass=mailAccount)','uid',0,'','(objectClass=posixGroup)',1,'memberuid','mail','sn','givenname','telephonenumber','','','',0,0,0,'',NULL,NULL,'','','2014-11-05 15:38:13','',1,1,'','','','','',0,0,0);
INSERT INTO `glpi_users` VALUES ('','yunoadminglpi','','',NULL,NULL,'','',0,NULL,0,NULL,1,NULL,1,4,'2014-11-05 15:38:44','2014-11-05 15:38:44','2014-11-05 15:38:44',0,0,0,0,0,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'uid=yunoadminglpi,ou=users,dc=yunohost,dc=org',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL);
INSERT INTO `glpi_profiles_users` VALUES ('',6,4,0,1,0);
UPDATE glpi_configs set ssovariables_id = 1 where id = 1;
UPDATE glpi_configs set url_base = 'https://yunodomainglpi' where id = 1;

