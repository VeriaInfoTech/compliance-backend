-- phpMyAdmin SQL Dump
-- version 5.1.1deb5ubuntu1
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: May 13, 2024 at 11:29 AM
-- Server version: 8.0.36-0ubuntu0.22.04.1
-- PHP Version: 8.2.15

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `risk`
--

-- --------------------------------------------------------

--
-- Table structure for table `erm_mandatory`
--

CREATE TABLE `erm_mandatory` (
                                 `id` int NOT NULL,
                                 `title` text CHARACTER SET utf8mb4 COLLATE utf8mb4_bin,
                                 `slug` text CHARACTER SET utf8mb4 COLLATE utf8mb4_bin,
                                 `information` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin
) ;

-- --------------------------------------------------------

--
-- Table structure for table `erm_mandatory_member`
--

CREATE TABLE `erm_mandatory_member` (
                                        `id` int NOT NULL,
                                        `user_id` int NOT NULL,
                                        `mandatory_unit` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin,
                                        `time_create` int NOT NULL DEFAULT '0',
                                        `time_update` int NOT NULL DEFAULT '0'
) ;

-- --------------------------------------------------------

--
-- Table structure for table `erm_meta`
--

CREATE TABLE `erm_meta` (
                            `id` int NOT NULL,
                            `user_id` int NOT NULL DEFAULT '0',
                            `target` json DEFAULT NULL,
                            `type` json DEFAULT NULL,
                            `slug` text COLLATE utf8mb4_bin,
                            `value` text COLLATE utf8mb4_bin,
                            `title` longtext COLLATE utf8mb4_bin,
                            `information` json DEFAULT NULL,
                            `time_create` int NOT NULL DEFAULT '0',
                            `time_update` int NOT NULL DEFAULT '0',
                            `time_delete` int NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;
COMMIT;


-- --------------------------------------------------------

--
-- Table structure for table `erm_risk_audit`
--

CREATE TABLE `erm_risk_audit` (
                                  `id` int UNSIGNED NOT NULL,
                                  `slug` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL,
                                  `standard_id` int UNSIGNED NOT NULL DEFAULT '0',
                                  `section_id` int UNSIGNED NOT NULL DEFAULT '0',
                                  `risk_id` int UNSIGNED NOT NULL DEFAULT '0',
                                  `user_id` int UNSIGNED NOT NULL DEFAULT '0',
                                  `company_id` int UNSIGNED NOT NULL DEFAULT '0',
                                  `time_create` int UNSIGNED NOT NULL DEFAULT '0',
                                  `time_update` int UNSIGNED NOT NULL DEFAULT '0',
                                  `level` varchar(32) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL DEFAULT '',
                                  `answer_score` int UNSIGNED NOT NULL DEFAULT '0',
                                  `answer_value` varchar(32) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL DEFAULT '',
                                  `answer_note` text CHARACTER SET utf8mb4 COLLATE utf8mb4_bin
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;

-- --------------------------------------------------------

--
-- Table structure for table `erm_risk_section`
--

CREATE TABLE `erm_risk_section` (
                                    `id` int UNSIGNED NOT NULL,
                                    `slug` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL,
                                    `standard_id` int UNSIGNED NOT NULL DEFAULT '0',
                                    `type` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL,
                                    `parent_id` int UNSIGNED NOT NULL DEFAULT '0',
                                    `code` varchar(32) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL DEFAULT '',
                                    `title` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL DEFAULT '',
                                    `status` int UNSIGNED NOT NULL DEFAULT '1',
                                    `time_create` int UNSIGNED NOT NULL DEFAULT '0',
                                    `time_update` int UNSIGNED NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;

-- --------------------------------------------------------

--
-- Table structure for table `erm_rule`
--

CREATE TABLE `erm_rule` (
                            `id` int NOT NULL,
                            `target` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL,
                            `mandatory_unit` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin,
                            `section_id` int DEFAULT NULL,
                            `code` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL,
                            `user_id` int DEFAULT '0',
                            `rule` text CHARACTER SET utf8mb4 COLLATE utf8mb4_bin,
                            `author` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL,
                            `approval_at` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL,
                            `cancellation_at` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL,
                            `promulgation_at` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL,
                            `is_creditable` int NOT NULL DEFAULT '1',
                            `type` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL,
                            `category` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL,
                            `validity` int NOT NULL DEFAULT '1',
                            `requirement` int NOT NULL DEFAULT '1',
                            `status` int NOT NULL DEFAULT '0',
                            `time_create` int NOT NULL DEFAULT '0',
                            `time_update` int NOT NULL DEFAULT '0',
                            `time_delete` int DEFAULT '0'
) ;

-- --------------------------------------------------------

--
-- Table structure for table `erm_task_audit`
--

CREATE TABLE `erm_task_audit` (
                                  `id` int UNSIGNED NOT NULL,
                                  `slug` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL,
                                  `standard_id` int UNSIGNED NOT NULL DEFAULT '0',
                                  `section_id` int UNSIGNED NOT NULL DEFAULT '0',
                                  `task_id` int UNSIGNED NOT NULL DEFAULT '0',
                                  `user_id` int UNSIGNED NOT NULL DEFAULT '0',
                                  `company_id` int UNSIGNED NOT NULL DEFAULT '0',
                                  `time_create` int UNSIGNED NOT NULL DEFAULT '0',
                                  `time_update` int UNSIGNED NOT NULL DEFAULT '0',
                                  `level` varchar(32) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL DEFAULT '',
                                  `answer_score` int UNSIGNED NOT NULL DEFAULT '0',
                                  `answer_value` varchar(32) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL DEFAULT '',
                                  `answer_note` text CHARACTER SET utf8mb4 COLLATE utf8mb4_bin
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;

-- --------------------------------------------------------

--
-- Table structure for table `erm_task_list`
--

CREATE TABLE `erm_task_list` (
                                 `id` int UNSIGNED NOT NULL,
                                 `parent_id` int DEFAULT '0',
                                 `type` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT 'compliance',
                                 `user_id` int DEFAULT '0',
                                 `standard_id` int UNSIGNED NOT NULL DEFAULT '0',
                                 `section_id` int UNSIGNED DEFAULT '0',
                                 `code` varchar(32) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT '',
                                 `title` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin,
                                 `description` text CHARACTER SET utf8mb4 COLLATE utf8mb4_bin,
                                 `value` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin,
                                 `rule_id` int DEFAULT NULL,
                                 `warranty_id` int DEFAULT NULL,
                                 `mandatory_unit` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin,
                                 `status` int UNSIGNED NOT NULL DEFAULT '1',
                                 `information` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin,
                                 `time_create` int UNSIGNED NOT NULL DEFAULT '0',
                                 `time_update` int UNSIGNED NOT NULL DEFAULT '0',
                                 `time_delete` int NOT NULL DEFAULT '0'
) ;

-- --------------------------------------------------------

--
-- Table structure for table `erm_task_progress`
--

CREATE TABLE `erm_task_progress` (
                                     `id` int UNSIGNED NOT NULL,
                                     `slug` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL,
                                     `type` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT 'single',
                                     `parent_id` int NOT NULL DEFAULT '0',
                                     `standard_id` int UNSIGNED NOT NULL DEFAULT '0',
                                     `section_id` int UNSIGNED NOT NULL DEFAULT '0',
                                     `task_id` int UNSIGNED NOT NULL DEFAULT '0',
                                     `user_id` int UNSIGNED NOT NULL DEFAULT '0',
                                     `assigner_id` int NOT NULL DEFAULT '0',
                                     `company_id` int UNSIGNED NOT NULL DEFAULT '0',
                                     `level` varchar(32) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL DEFAULT '',
                                     `answer_score` int UNSIGNED DEFAULT '0',
                                     `answer_value` varchar(32) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT '',
                                     `answer_note` text CHARACTER SET utf8mb4 COLLATE utf8mb4_bin,
                                     `history` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin,
                                     `status` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT 'doing',
                                     `time_deadline` int DEFAULT '0',
                                     `information` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin,
                                     `time_create` int UNSIGNED NOT NULL DEFAULT '0',
                                     `time_update` int UNSIGNED NOT NULL DEFAULT '0',
                                     `time_delete` int NOT NULL DEFAULT '0'
) ;

-- --------------------------------------------------------

--
-- Table structure for table `erm_task_risk`
--

CREATE TABLE `erm_task_risk` (
                                 `id` int UNSIGNED NOT NULL,
                                 `slug` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL,
                                 `type` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT 'single',
                                 `parent_id` int DEFAULT '0',
                                 `task_id` int UNSIGNED NOT NULL DEFAULT '0',
                                 `user_id` int UNSIGNED NOT NULL DEFAULT '0',
                                 `assigner_id` int NOT NULL DEFAULT '0',
                                 `standard_id` int UNSIGNED DEFAULT '0',
                                 `section_id` int UNSIGNED DEFAULT '0',
                                 `company_id` int UNSIGNED DEFAULT '0',
                                 `rule_id` int DEFAULT '0',
                                 `warranty_id` int DEFAULT '0',
                                 `control` text CHARACTER SET utf8mb4 COLLATE utf8mb4_bin,
                                 `level` varchar(32) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL DEFAULT '',
                                 `risk_intensity` int UNSIGNED DEFAULT '0',
                                 `risk_effect` int UNSIGNED DEFAULT '0',
                                 `risk_data` int DEFAULT NULL,
                                 `risk_threat` text CHARACTER SET utf8mb4 COLLATE utf8mb4_bin,
                                 `risk_damage` text CHARACTER SET utf8mb4 COLLATE utf8mb4_bin,
                                 `risk_response_type` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL COMMENT 'accept,decrease,transfer,reject',
                                 `risk_execution_percent` int DEFAULT NULL,
                                 `risk_proposed_action` mediumtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin,
                                 `risk_scenario` text CHARACTER SET utf8mb4 COLLATE utf8mb4_bin,
                                 `history` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin,
                                 `status` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT 'doing',
                                 `time_deadline` int NOT NULL DEFAULT '0',
                                 `time_create` int UNSIGNED NOT NULL DEFAULT '0',
                                 `time_update` int UNSIGNED NOT NULL DEFAULT '0',
                                 `time_delete` int NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `erm_task_section`
--

CREATE TABLE `erm_task_section` (
                                    `id` int UNSIGNED NOT NULL,
                                    `slug` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL,
                                    `standard_id` int UNSIGNED NOT NULL DEFAULT '0',
                                    `type` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL,
                                    `parent_id` int UNSIGNED NOT NULL DEFAULT '0',
                                    `code` varchar(32) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL DEFAULT '',
                                    `title` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL DEFAULT '',
                                    `status` int UNSIGNED NOT NULL DEFAULT '1',
                                    `time_create` int UNSIGNED NOT NULL DEFAULT '0',
                                    `time_update` int UNSIGNED NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;

-- --------------------------------------------------------

--
-- Table structure for table `erm_warranty_types`
--

CREATE TABLE `erm_warranty_types` (
                                      `id` int NOT NULL,
                                      `slug` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL,
                                      `title` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `erm_mandatory`
--
ALTER TABLE `erm_mandatory`
    ADD PRIMARY KEY (`id`);

--
-- Indexes for table `erm_mandatory_member`
--
ALTER TABLE `erm_mandatory_member`
    ADD PRIMARY KEY (`id`);

--
-- Indexes for table `erm_risk_audit`
--
ALTER TABLE `erm_risk_audit`
    ADD PRIMARY KEY (`id`);

--
-- Indexes for table `erm_risk_section`
--
ALTER TABLE `erm_risk_section`
    ADD PRIMARY KEY (`id`);

--
-- Indexes for table `erm_rule`
--
ALTER TABLE `erm_rule`
    ADD PRIMARY KEY (`id`);

--
-- Indexes for table `erm_task_audit`
--
ALTER TABLE `erm_task_audit`
    ADD PRIMARY KEY (`id`);

--
-- Indexes for table `erm_task_list`
--
ALTER TABLE `erm_task_list`
    ADD PRIMARY KEY (`id`);

--
-- Indexes for table `erm_task_progress`
--
ALTER TABLE `erm_task_progress`
    ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `slug` (`slug`);

--
-- Indexes for table `erm_task_risk`
--
ALTER TABLE `erm_task_risk`
    ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `slug` (`slug`);

--
-- Indexes for table `erm_task_section`
--
ALTER TABLE `erm_task_section`
    ADD PRIMARY KEY (`id`);

--
-- Indexes for table `erm_warranty_types`
--
ALTER TABLE `erm_warranty_types`
    ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `erm_mandatory`
--
ALTER TABLE `erm_mandatory`
    MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `erm_mandatory_member`
--
ALTER TABLE `erm_mandatory_member`
    MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `erm_risk_audit`
--
ALTER TABLE `erm_risk_audit`
    MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `erm_risk_section`
--
ALTER TABLE `erm_risk_section`
    MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `erm_rule`
--
ALTER TABLE `erm_rule`
    MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `erm_task_audit`
--
ALTER TABLE `erm_task_audit`
    MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `erm_task_list`
--
ALTER TABLE `erm_task_list`
    MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `erm_task_progress`
--
ALTER TABLE `erm_task_progress`
    MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `erm_task_risk`
--
ALTER TABLE `erm_task_risk`
    MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `erm_task_section`
--
ALTER TABLE `erm_task_section`
    MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `erm_warranty_types`
--
ALTER TABLE `erm_warranty_types`
    MODIFY `id` int NOT NULL AUTO_INCREMENT;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;