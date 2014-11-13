/*
SQLyog Ultimate v11.52 (64 bit)
MySQL - 5.1.72-community : Database - database
*********************************************************************
*/


/*!40101 SET NAMES utf8 */;

/*!40101 SET SQL_MODE=''*/;

/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;
CREATE DATABASE /*!32312 IF NOT EXISTS*/`database` /*!40100 DEFAULT CHARACTER SET utf8 */;

USE `database`;

/*Table structure for table `gallery` */

DROP TABLE IF EXISTS `gallery`;

CREATE TABLE `gallery` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `GalleryName` varchar(256) DEFAULT NULL,
  `GalleryFolder` varchar(256) DEFAULT NULL,
  PRIMARY KEY (`ID`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8;

/*Table structure for table `gallery_groups` */

DROP TABLE IF EXISTS `gallery_groups`;

CREATE TABLE `gallery_groups` (
  `GroupID` int(11) NOT NULL AUTO_INCREMENT,
  `GalleryID` int(11) DEFAULT NULL,
  `GroupTitle` varchar(500) DEFAULT NULL,
  `Folder` varchar(500) DEFAULT NULL,
  `Active` tinyint(4) DEFAULT NULL,
  PRIMARY KEY (`GroupID`)
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8;

/*Table structure for table `gallery_pics` */

DROP TABLE IF EXISTS `gallery_pics`;

CREATE TABLE `gallery_pics` (
  `PictureID` int(11) NOT NULL AUTO_INCREMENT,
  `PictureSRC` varchar(500) DEFAULT NULL,
  `PictureThumb` varchar(500) DEFAULT NULL,
  `PictureTitle` varchar(500) DEFAULT NULL,
  `PictureActive` tinyint(1) DEFAULT NULL,
  `CoverPhoto` tinyint(1) DEFAULT '0',
  `OrderID` int(11) NOT NULL,
  `CreatedOn` datetime DEFAULT NULL,
  `ModifiedOn` datetime DEFAULT NULL,
  PRIMARY KEY (`PictureID`)
) ENGINE=InnoDB AUTO_INCREMENT=205 DEFAULT CHARSET=utf8;

/*Table structure for table `gallery_pics_bridge_groups` */

DROP TABLE IF EXISTS `gallery_pics_bridge_groups`;

CREATE TABLE `gallery_pics_bridge_groups` (
  `BridgeID` int(11) NOT NULL AUTO_INCREMENT,
  `bGroupID` int(11) DEFAULT NULL,
  `bPictureID` int(11) DEFAULT NULL,
  PRIMARY KEY (`BridgeID`)
) ENGINE=InnoDB AUTO_INCREMENT=202 DEFAULT CHARSET=utf8;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;
