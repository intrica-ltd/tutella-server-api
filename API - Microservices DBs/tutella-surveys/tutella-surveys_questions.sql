-- MySQL dump 10.13  Distrib 5.7.23, for Linux (x86_64)
--
-- Host: localhost    Database: tutella-surveys
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
-- Table structure for table `questions`
--

DROP TABLE IF EXISTS `questions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `questions` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `survey_id` int(11) NOT NULL,
  `question` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `answer_type` int(11) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=784 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `questions`
--

LOCK TABLES `questions` WRITE;
/*!40000 ALTER TABLE `questions` DISABLE KEYS */;
INSERT INTO `questions` VALUES (697,268,'Test 123',1,'2019-02-21 10:18:16','2019-02-21 10:18:16'),(698,268,'Test asd asd',2,'2019-02-21 10:18:16','2019-02-21 10:18:16'),(699,268,'Test asd asd',3,'2019-02-21 10:18:16','2019-02-21 10:18:16'),(700,268,'asd asd',4,'2019-02-21 10:18:16','2019-02-21 10:18:16'),(701,269,'Test 123',1,'2019-02-21 10:37:49','2019-02-21 10:37:49'),(702,269,'Test asd asd',2,'2019-02-21 10:37:49','2019-02-21 10:37:49'),(703,269,'Test asd asd',3,'2019-02-21 10:37:49','2019-02-21 10:37:49'),(704,269,'asd asd',4,'2019-02-21 10:37:49','2019-02-21 10:37:49'),(705,270,'Question 1',1,'2019-02-21 13:17:11','2019-02-21 13:17:11'),(706,270,'Question 2',4,'2019-02-21 13:17:11','2019-02-21 13:17:11'),(707,270,'Question 3',3,'2019-02-21 13:17:11','2019-02-21 13:17:11'),(708,271,'How was your host family',1,'2019-03-16 21:03:47','2019-03-16 21:03:47'),(709,271,'How was your buddy',2,'2019-03-16 21:03:47','2019-03-16 21:03:47'),(710,271,'Did you like the trips after school',3,'2019-03-16 21:03:47','2019-03-16 21:03:47'),(711,271,'Did you like Brighton',3,'2019-03-16 21:03:47','2019-03-16 21:03:47'),(712,271,'How was your packed lunch',2,'2019-03-16 21:03:47','2019-03-16 21:03:47'),(713,271,'How was the trip to London',1,'2019-03-16 21:03:47','2019-03-16 21:03:47'),(714,271,'Would you come back to Brighton',1,'2019-03-16 21:03:47','2019-03-16 21:03:47'),(715,271,'Tell us about your experience',4,'2019-03-16 21:03:47','2019-03-16 21:03:47'),(716,272,'How is your host family?',1,'2019-03-21 19:29:56','2019-03-21 19:29:56'),(717,272,'How is your packed lunch?',1,'2019-03-21 19:29:56','2019-03-21 19:29:56'),(718,272,'How is your buddy?',1,'2019-03-21 19:29:56','2019-03-21 19:29:56'),(719,272,'How are the activities after school?',3,'2019-03-21 19:29:56','2019-03-21 19:29:56'),(720,272,'Do you like Brighton?',3,'2019-03-21 19:29:56','2019-03-21 19:29:56'),(721,272,'How is dinner at host family?',3,'2019-03-21 19:29:56','2019-03-21 19:29:56'),(722,272,'Would you return to Brighton?',3,'2019-03-21 19:29:56','2019-03-21 19:29:56'),(723,272,'Tell us any interesting details about your stay?',4,'2019-03-21 19:29:56','2019-03-21 19:29:56'),(724,273,'How is your host family?',1,'2019-03-21 19:35:34','2019-03-21 19:35:34'),(725,273,'How is your packed lunch?',1,'2019-03-21 19:35:34','2019-03-21 19:35:34'),(726,273,'How is your buddy?',1,'2019-03-21 19:35:34','2019-03-21 19:35:34'),(727,273,'How are the activities after school?',3,'2019-03-21 19:35:34','2019-03-21 19:35:34'),(728,273,'Do you like Brighton?',3,'2019-03-21 19:35:34','2019-03-21 19:35:34'),(729,273,'How is dinner at host family?',3,'2019-03-21 19:35:34','2019-03-21 19:35:34'),(730,273,'Would you return to Brighton?',3,'2019-03-21 19:35:34','2019-03-21 19:35:34'),(731,273,'Tell us any interesting details about your stay?',4,'2019-03-21 19:35:34','2019-03-21 19:35:34'),(732,274,'Test 123',1,'2019-06-25 15:19:37','2019-06-25 15:19:37'),(733,274,'Test 123 12',2,'2019-06-25 15:19:37','2019-06-25 15:19:37'),(734,275,'Test 123',1,'2019-06-26 10:56:43','2019-06-26 10:56:43'),(735,275,'Test 123 12',2,'2019-06-26 10:56:43','2019-06-26 10:56:43'),(736,276,'Test 123',1,'2019-06-26 11:44:30','2019-06-26 11:44:30'),(737,276,'Test 333',2,'2019-06-26 11:44:30','2019-06-26 11:44:30'),(738,276,'Test 432',3,'2019-06-26 11:44:30','2019-06-26 11:44:30'),(739,276,'Test 5432',4,'2019-06-26 11:44:30','2019-06-26 11:44:30'),(740,277,'Question 1',1,'2019-06-27 11:39:56','2019-06-27 11:39:56'),(741,277,'Question 2',4,'2019-06-27 11:39:56','2019-06-27 11:39:56'),(742,277,'Question 3',2,'2019-06-27 11:39:56','2019-06-27 11:39:56'),(743,277,'Question 4',3,'2019-06-27 11:39:56','2019-06-27 11:39:56'),(744,278,'Test 123',1,'2019-06-28 11:22:57','2019-06-28 11:22:57'),(745,278,'Test 123 12',2,'2019-06-28 11:22:57','2019-06-28 11:22:57'),(746,279,'Test 123',1,'2019-06-28 11:24:19','2019-06-28 11:24:19'),(747,279,'Test 123 12',2,'2019-06-28 11:24:19','2019-06-28 11:24:19'),(748,280,'question 1',1,'2019-06-28 11:31:38','2019-06-28 11:31:38'),(749,280,'question 2',1,'2019-06-28 11:31:38','2019-06-28 11:31:38'),(750,280,'question 3',2,'2019-06-28 11:31:38','2019-06-28 11:31:38'),(751,281,'Test 123',1,'2019-06-28 12:14:51','2019-06-28 12:14:51'),(752,281,'Test 123 12',2,'2019-06-28 12:14:51','2019-06-28 12:14:51'),(753,282,'rerere',1,'2019-06-28 14:33:11','2019-06-28 14:33:11'),(754,282,'errere',2,'2019-06-28 14:33:11','2019-06-28 14:33:11'),(755,282,'errere',3,'2019-06-28 14:33:11','2019-06-28 14:33:11'),(756,282,'errerre',4,'2019-06-28 14:33:11','2019-06-28 14:33:11'),(757,283,'Question 1',1,'2019-07-01 13:37:11','2019-07-01 13:37:11'),(758,283,'Question 2',2,'2019-07-01 13:37:11','2019-07-01 13:37:11'),(759,283,'Question 3',4,'2019-07-01 13:37:11','2019-07-01 13:37:11'),(760,283,'Question 4',3,'2019-07-01 13:37:11','2019-07-01 13:37:11'),(761,284,'asda',1,'2019-07-01 14:03:56','2019-07-01 14:03:56'),(762,285,'Afg',1,'2019-07-01 14:17:16','2019-07-01 14:17:16'),(763,286,'Do you like this app?',1,'2019-07-05 21:20:38','2019-07-05 21:20:38'),(764,286,'Do you like scampi?',1,'2019-07-05 21:20:38','2019-07-05 21:20:38'),(765,287,'Do you like the weather today?',2,'2019-07-06 11:24:06','2019-07-06 11:24:06'),(766,287,'What. Is your favourite food?',4,'2019-07-06 11:24:06','2019-07-06 11:24:06'),(767,288,'How much do you like London?',3,'2019-07-06 11:30:43','2019-07-06 11:30:43'),(768,288,'How much do you like Taunton?',2,'2019-07-06 11:30:43','2019-07-06 11:30:43'),(769,289,'How do you rate your trip to Tower Bridge?',1,'2019-07-07 17:59:53','2019-07-07 17:59:53'),(770,290,'Have you  had a good day today?',1,'2019-07-07 20:16:34','2019-07-07 20:16:34'),(771,291,'Did you get this before  midday today?',1,'2019-07-08 11:49:05','2019-07-08 11:49:05'),(772,292,'You okay?',1,'2019-07-08 17:52:17','2019-07-08 17:52:17'),(773,293,'You okay?',1,'2019-07-08 17:55:44','2019-07-08 17:55:44'),(774,294,'You okay?',1,'2019-07-08 17:55:45','2019-07-08 17:55:45'),(775,295,'Am i okay?',3,'2019-07-08 18:02:10','2019-07-08 18:02:10'),(776,296,'Looking forward to steak?',1,'2019-07-08 18:04:19','2019-07-08 18:04:19'),(777,297,'Did you get this before 1830?',1,'2019-07-08 18:24:35','2019-07-08 18:24:35'),(778,298,'and this?',3,'2019-07-08 18:25:11','2019-07-08 18:25:11'),(779,299,'Is the sun still shining?',1,'2019-07-08 18:27:24','2019-07-08 18:27:24'),(780,300,'You okay this evening?',1,'2019-07-08 19:45:37','2019-07-08 19:45:37'),(781,301,'How was the peri peri?',1,'2019-07-09 19:50:18','2019-07-09 19:50:18'),(782,301,'Have you sent me Beth’s number yet?',2,'2019-07-09 19:50:18','2019-07-09 19:50:18'),(783,302,'Sent at 1555. What time did you receive ?',4,'2019-07-11 15:54:20','2019-07-11 15:54:20');
/*!40000 ALTER TABLE `questions` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2019-07-17 10:20:49
