
CREATE TABLE IF NOT EXISTS `findmefollow` ( `grpnum` BIGINT( 11 ) NOT NULL , `strategy` VARCHAR( 50 ) NOT NULL , `grptime` SMALLINT NOT NULL , `grppre` VARCHAR( 100 ) NULL , `grplist` VARCHAR( 255 ) NOT NULL , `annmsg` VARCHAR( 255 ) NULL , `postdest` VARCHAR( 255 ) NULL , `dring` VARCHAR ( 50 ) NULL , remotealert VARCHAR ( 80 ), needsconf VARCHAR ( 10 ), toolate VARCHAR ( 80 ), pre_ring SMALLINT NOT NULL DEFAULT 0, PRIMARY KEY  (`grpnum`) ) TYPE = MYISAM ;

