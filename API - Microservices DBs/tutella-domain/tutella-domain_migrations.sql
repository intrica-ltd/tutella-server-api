-- MySQL dump 10.13  Distrib 5.7.23, for Linux (x86_64)
--
-- Host: localhost    Database: tutella-domain
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
-- Table structure for table `migrations`
--

DROP TABLE IF EXISTS `migrations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `migrations` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `migration` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `batch` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=30 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `migrations`
--

LOCK TABLES `migrations` WRITE;
/*!40000 ALTER TABLE `migrations` DISABLE KEYS */;
INSERT INTO `migrations` VALUES (1,'2018_03_28_100103_create_app_access_token_table',1),(2,'2018_03_28_141658_create_roles_table',1),(3,'2018_03_28_142533_create_role_user_table',1),(4,'2018_03_30_114904_create_users_table',1),(5,'2018_04_02_105437_create_enrollment_codes_table',1),(6,'2018_04_18_130907_add_api_token_to_users',1),(7,'2018_04_20_105250_add_enrollment_code_to_users_table',1),(8,'2018_04_24_134414_remove_api_token_from_users',1),(9,'2018_04_24_134546_make_table_user_tokens',1),(10,'2018_04_24_151438_add_school_id_to_users',1),(11,'2018_04_26_111236_add_phone_to_users_table',1),(12,'2018_04_26_125847_add_invited_by_users',1),(13,'2018_05_03_130638_add_fb_id_users',1),(14,'2018_05_04_110318_add_invited_by_name',1),(15,'2018_05_10_092008_add_expiar_date__user_token',1),(16,'2018_05_31_102425_create_social_accounts_table',1),(17,'2018_06_25_153519_alter_table_users_add_image_id_column',1),(18,'2018_07_03_124441_add_table_social_feeds',2),(19,'2018_07_04_101950_drop_social_accounts_table',2),(20,'2018_08_08_092515_alter_table_users_add_insta_user_id_column',3),(21,'2018_08_30_114334_create_table_billing_packages',3),(22,'2018_08_30_114353_create_table_school_package',3),(23,'2018_08_30_215552_add_firebase_token_column_on_users_table',3),(24,'2018_09_02_162544_create_table_bank_account_details',4),(25,'2018_09_02_163521_create_table_company_details',4),(26,'2018_10_12_154130_alter_table_users_add_welcome_msg_column',5),(27,'2018_10_31_133220_alter_table_enrollment_codes_add_school_id_column',6),(28,'2019_01_24_141102_alter_table_users_add_username_row',7),(29,'2019_02_20_134845_alter_table_users_add_fb_insta_user_columns',8);
/*!40000 ALTER TABLE `migrations` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2019-07-17 10:19:05
