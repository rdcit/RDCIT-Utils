-- RDCIT DATAPLAN COMPOSER DATABASE TABLES
--
-- ------------------------------------------------------


DROP TABLE IF EXISTS `data_item_response_codes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `data_item_response_codes` (
  `unique_code` int(17) NOT NULL AUTO_INCREMENT,
  `type` varchar(50) DEFAULT NULL,
  `label` varchar(255) DEFAULT NULL,
  `text` text,
  `values` text,
  `data_type` varchar(25) DEFAULT NULL,
  `default_value` varchar(100) DEFAULT NULL,
  PRIMARY KEY (`unique_code`)
) ENGINE=InnoDB AUTO_INCREMENT=20 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

DROP TABLE IF EXISTS `data_item_response_options`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `data_item_response_options` (
  `unique_id` int(17) NOT NULL AUTO_INCREMENT,
  `concept` varchar(255) DEFAULT NULL,
  `concept_group` varchar(255) DEFAULT NULL,
  `data_item_id` int(17) NOT NULL DEFAULT '0',
  `data_item_name` varchar(255) CHARACTER SET latin1 NOT NULL,
  `description` varchar(255) DEFAULT NULL,
  `option_name` varchar(255) NOT NULL,
  `data_type` varchar(4) DEFAULT NULL,
  `unit` varchar(50) CHARACTER SET latin1 DEFAULT NULL,
  `unique_code` int(17) DEFAULT NULL,
  `response_options` text,
  `response_values` text,
  `description_label` varchar(255) CHARACTER SET latin1 NOT NULL,
  `ontology_type` varchar(255) CHARACTER SET latin1 DEFAULT NULL,
  `ontology_term` varchar(255) CHARACTER SET latin1 NOT NULL,
  `ontology_code` varchar(255) CHARACTER SET latin1 NOT NULL,
  `ontology_modifier` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`unique_id`)
) ENGINE=InnoDB AUTO_INCREMENT=2765 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;
