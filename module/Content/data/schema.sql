
--
-- Table structure for table `content_item`
--

CREATE TABLE `content_item` (
                                `id` int UNSIGNED NOT NULL,
                                `title` varchar(255) NOT NULL DEFAULT '',
                                `slug` varchar(255) NOT NULL DEFAULT '',
                                `type` varchar(64) DEFAULT NULL,
                                `status` int UNSIGNED NOT NULL DEFAULT '0',
                                `user_id` int UNSIGNED NOT NULL DEFAULT '0',
                                `time_create` int UNSIGNED NOT NULL DEFAULT '0',
                                `time_update` int UNSIGNED NOT NULL DEFAULT '0',
                                `time_delete` int UNSIGNED NOT NULL DEFAULT '0',
                                `information` json DEFAULT NULL,
                                `priority` int DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `content_log`
--

CREATE TABLE `content_log` (
                               `id` int NOT NULL,
                               `user_id` int NOT NULL DEFAULT '0',
                               `item_id` int NOT NULL DEFAULT '0',
                               `action` varchar(255) NOT NULL DEFAULT '',
                               `type` varchar(255) NOT NULL DEFAULT '',
                               `event` varchar(255) NOT NULL DEFAULT '',
                               `date` varchar(255) NOT NULL DEFAULT '',
                               `time_create` int NOT NULL DEFAULT '0',
                               `time_update` int NOT NULL DEFAULT '0',
                               `time_delete` int NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `content_meta_key`
--

CREATE TABLE `content_meta_key` (
                                    `id` int UNSIGNED NOT NULL,
                                    `key` varchar(64) NOT NULL DEFAULT '',
                                    `target` varchar(255) DEFAULT NULL,
                                    `value` varchar(255) DEFAULT NULL,
                                    `type` varchar(64) NOT NULL DEFAULT 'string',
                                    `suffix` varchar(255) NOT NULL DEFAULT '',
                                    `option` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin,
                                    `logo` varchar(255) NOT NULL DEFAULT 'no-image.png',
                                    `status` int UNSIGNED NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `content_meta_value`
--

CREATE TABLE `content_meta_value` (
                                      `id` int UNSIGNED NOT NULL,
                                      `item_id` int UNSIGNED NOT NULL DEFAULT '0',
                                      `item_slug` varchar(255) DEFAULT NULL,
                                      `meta_key` varchar(64) NOT NULL DEFAULT '''''',
                                      `value_string` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT ''' ''',
                                      `value_number` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT ''' ''',
                                      `value_id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT ''' ''',
                                      `value_slug` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT ''' ''',
                                      `status` int UNSIGNED NOT NULL DEFAULT '0',
                                      `logo` varchar(255) NOT NULL DEFAULT 'no-image.png',
                                      `time_create` int UNSIGNED NOT NULL DEFAULT '0',
                                      `time_update` int NOT NULL DEFAULT '0',
                                      `time_delete` int NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `content_item`
--
ALTER TABLE `content_item`
    ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `slug` (`slug`);

--
-- Indexes for table `content_meta_key`
--
ALTER TABLE `content_meta_key`
    ADD PRIMARY KEY (`id`);

--
-- Indexes for table `content_meta_value`
--
ALTER TABLE `content_meta_value`
    ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `content_item`
--
ALTER TABLE `content_item`
    MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `content_meta_key`
--
ALTER TABLE `content_meta_key`
    MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `content_meta_value`
--
ALTER TABLE `content_meta_value`
    MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT;
COMMIT;
