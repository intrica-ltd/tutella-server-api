-- MySQL dump 10.13  Distrib 5.7.23, for Linux (x86_64)
--
-- Host: localhost    Database: tutella-auth
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
-- Table structure for table `role_user`
--

DROP TABLE IF EXISTS `role_user`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `role_user` (
  `user_id` int(10) unsigned NOT NULL,
  `role_id` int(10) unsigned NOT NULL,
  PRIMARY KEY (`user_id`,`role_id`),
  KEY `role_user_role_id_foreign` (`role_id`),
  CONSTRAINT `role_user_role_id_foreign` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `role_user_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `role_user`
--

LOCK TABLES `role_user` WRITE;
/*!40000 ALTER TABLE `role_user` DISABLE KEYS */;
INSERT INTO `role_user` VALUES (1,1),(425,2),(438,2),(439,2),(441,2),(452,2),(453,2),(454,2),(455,2),(456,2),(457,2),(460,2),(463,2),(464,2),(465,2),(482,2),(483,2),(484,2),(485,2),(492,2),(495,2),(498,2),(499,2),(500,2),(518,2),(543,2),(544,2),(545,2),(432,3),(434,3),(437,3),(442,3),(443,3),(444,3),(445,3),(446,3),(501,3),(505,3),(511,3),(516,3),(517,3),(521,3),(522,3),(523,3),(524,3),(526,3),(529,3),(532,3),(535,3),(536,3),(540,3),(541,3),(542,3),(551,3),(554,3),(556,3),(557,3),(559,3),(426,4),(428,4),(430,4),(431,4),(433,4),(435,4),(436,4),(440,4),(447,4),(448,4),(449,4),(450,4),(451,4),(458,4),(459,4),(461,4),(462,4),(466,4),(467,4),(468,4),(469,4),(470,4),(471,4),(472,4),(473,4),(474,4),(475,4),(476,4),(477,4),(478,4),(479,4),(480,4),(481,4),(486,4),(487,4),(488,4),(489,4),(490,4),(491,4),(493,4),(494,4),(496,4),(497,4),(502,4),(503,4),(504,4),(506,4),(507,4),(508,4),(509,4),(510,4),(512,4),(513,4),(514,4),(515,4),(519,4),(520,4),(525,4),(527,4),(528,4),(530,4),(531,4),(533,4),(534,4),(537,4),(538,4),(547,4),(549,4),(550,4),(552,4),(553,4),(555,4),(558,4),(560,4),(561,4);
/*!40000 ALTER TABLE `role_user` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2019-07-17 10:17:24
