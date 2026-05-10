-- Migration: Add `key` column to permission tables if missing
-- Run this if you get: Unknown column 'key' in 'WHERE'
-- If you get "Duplicate column name 'key'", the column already exists; ensure all three tables have it.

-- permission_resource: add `key` (MySQL reserved word, must be backtick-quoted)
ALTER TABLE `permission_resource`
    ADD COLUMN `key` VARCHAR(255) NULL DEFAULT NULL AFTER `title`;

-- permission_page: add `key`
ALTER TABLE `permission_page`
    ADD COLUMN `key` VARCHAR(255) NULL DEFAULT NULL AFTER `title`;

-- permission_role: add `key`
ALTER TABLE `permission_role`
    ADD COLUMN `key` VARCHAR(255) NULL DEFAULT NULL AFTER `id`;

-- If tables don't exist, run the full schema first: module/User/data/schema.sql
