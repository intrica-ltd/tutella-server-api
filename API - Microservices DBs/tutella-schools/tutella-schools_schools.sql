-- MySQL dump 10.13  Distrib 5.7.23, for Linux (x86_64)
--
-- Host: localhost    Database: tutella-schools
-- ------------------------------------------------------
-- Server version	5.7.22-0ubuntu0.17.10.1

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `schools`
--

DROP TABLE IF EXISTS `schools`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `schools` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `school_name` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `address` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `address_lat` text COLLATE utf8mb4_unicode_ci,
  `address_lng` text COLLATE utf8mb4_unicode_ci,
  `email` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `phone` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `logo` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `logo_id` int(11) DEFAULT NULL,
  `active` tinyint(1) NOT NULL DEFAULT '1',
  `fb_access_token` text COLLATE utf8mb4_unicode_ci,
  `token_created_at` timestamp NULL DEFAULT NULL,
  `fb_page_id` bigint(20) DEFAULT NULL,
  `fb_page_url` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `cancelled_at` timestamp NULL DEFAULT NULL,
  `enrollment_code` text COLLATE utf8mb4_unicode_ci,
  `poster_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=64 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `schools`
--

LOCK TABLES `schools` WRITE;
/*!40000 ALTER TABLE `schools` DISABLE KEYS */;
INSERT INTO `schools` VALUES (42,425,'Lile\'s School','School Address',NULL,NULL,'lile@devsy.com','083289982','41e1cbaa900ccd0f0753aba72ed11fcb.jpg',1150,1,NULL,NULL,NULL,NULL,'2019-02-20 12:56:49','2019-06-18 16:25:36',NULL,'640993',994),(43,439,'TEST SCHOOL','Upper Bognor Road',NULL,NULL,'graham@reddie.com','7771665438',NULL,NULL,1,NULL,NULL,NULL,NULL,'2019-02-22 14:28:30','2019-03-18 16:00:34',NULL,'427922',1005),(44,441,'UKHSI','C/O Paca, Chalky Road, Brighton, BN41 2WS',NULL,NULL,'alex@ukhsi.com','01273921615',NULL,NULL,1,NULL,NULL,NULL,NULL,'2019-02-25 16:34:20','2019-02-28 08:18:53',NULL,'553130',985),(45,452,'school','Skopje',NULL,NULL,'nikolinacvetanovska@hotmail.com','55555',NULL,NULL,1,NULL,NULL,NULL,NULL,'2019-03-07 08:44:53','2019-03-07 08:44:53',NULL,NULL,NULL),(46,453,'cccc','ffff',NULL,NULL,'cvetanovskanikolina@gmail.com','4455',NULL,NULL,1,NULL,NULL,NULL,NULL,'2019-03-07 08:56:21','2019-03-07 08:56:21',NULL,NULL,NULL),(47,454,'fdhgfdsg','ggg',NULL,NULL,'cvetanovskanikolina@gmail.com','5555',NULL,NULL,1,NULL,NULL,NULL,NULL,'2019-03-07 09:03:16','2019-03-07 09:03:16',NULL,NULL,NULL),(48,455,'Elizabeta Petrevska','ask',NULL,NULL,'elizabeta.petrevska@devsy.com','11111111',NULL,NULL,1,NULL,NULL,NULL,NULL,'2019-03-07 09:17:07','2019-03-07 09:17:07',NULL,NULL,NULL),(49,456,'Isahr','isahr',NULL,NULL,'lsahr_1216@omdo.xyz','123456789',NULL,NULL,1,NULL,NULL,NULL,NULL,'2019-03-07 13:27:17','2019-03-07 13:27:17',NULL,NULL,NULL),(50,457,'Rhabibah','Rhabibah',NULL,NULL,'rhabibah_ch143q@d-link.gq','123456789',NULL,NULL,1,NULL,NULL,NULL,NULL,'2019-03-07 14:29:38','2019-03-07 14:29:38',NULL,NULL,NULL),(51,460,'Sue Freeman','Portslade',NULL,NULL,'smiloud.hadjd@semail.us','1273921615',NULL,NULL,1,NULL,NULL,NULL,NULL,'2019-03-07 14:58:06','2019-03-07 14:58:06',NULL,NULL,NULL),(52,463,'ana\'s school','address',NULL,NULL,'anastasija.davitkova@devsy.com','123456',NULL,NULL,1,NULL,NULL,NULL,NULL,'2019-03-08 09:15:15','2019-03-08 09:15:15',NULL,NULL,NULL),(53,464,'Schol','dsg',NULL,NULL,'nikolinacvetanovska13@gmail.com','5555',NULL,NULL,1,NULL,NULL,NULL,NULL,'2019-03-08 09:19:53','2019-03-08 09:19:53',NULL,NULL,NULL),(54,465,'University','TEst',NULL,NULL,'millerf81@gmail.com',NULL,NULL,NULL,-1,NULL,NULL,NULL,NULL,'2019-03-14 10:57:23','2019-03-14 10:58:35','2019-03-14 10:58:35',NULL,NULL),(55,483,'Test','test',NULL,NULL,'nikolina@apps4u.com','555',NULL,NULL,1,NULL,NULL,NULL,NULL,'2019-03-19 13:19:02','2019-03-19 13:19:02',NULL,NULL,NULL),(56,484,'School','School',NULL,NULL,'nikolina@ukhsi.com','55555',NULL,NULL,1,NULL,NULL,NULL,NULL,'2019-03-19 13:32:13','2019-03-19 13:32:13',NULL,NULL,NULL),(57,485,'Test','Test',NULL,NULL,'ssjcj24@sixxx.ga','5555',NULL,NULL,1,NULL,NULL,NULL,NULL,'2019-03-20 09:57:55','2019-03-20 09:57:55',NULL,NULL,NULL),(58,492,'Meesh\'s School','Anywhere',NULL,NULL,'michele@tutella.io','07973376635','7789e4b103ddf3eaba6d6b5cde430d26.png',1143,1,NULL,NULL,NULL,NULL,'2019-06-09 22:21:45','2019-06-09 23:12:14',NULL,'354034',1144),(59,495,'Test','Test Address',NULL,NULL,'gjorgjievska.lile@gmail.com',NULL,'e9f8b3d53d3af9a8e2697027d1c33b93.png',1145,1,NULL,NULL,NULL,NULL,'2019-06-11 07:02:18','2019-06-11 07:06:03',NULL,'717501',1146),(60,499,'Test School','Test Address',NULL,NULL,'lile@devsy.com',NULL,NULL,NULL,1,NULL,NULL,NULL,NULL,'2019-06-13 13:12:09','2019-06-13 13:12:09',NULL,NULL,NULL),(61,500,'Test School','Test Address',NULL,NULL,'lile@devsy.com',NULL,NULL,NULL,1,NULL,NULL,NULL,NULL,'2019-06-13 13:29:34','2019-06-13 13:29:34',NULL,NULL,NULL),(62,518,'Lile Gjorgjievska LALALALALALA','Teodosij Golaganov, 192',NULL,NULL,'lile@devsy.com','-',NULL,NULL,1,NULL,NULL,NULL,NULL,'2019-06-28 11:48:44','2019-06-28 11:50:32',NULL,NULL,NULL),(63,545,'MB Test School','Blah blah',NULL,NULL,'michele.bacchus@yahoo.co.uk','07973376635',NULL,NULL,1,NULL,NULL,NULL,NULL,'2019-07-02 14:59:55','2019-07-02 15:09:01',NULL,'657338',1155);
/*!40000 ALTER TABLE `schools` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2019-07-17 10:20:15
