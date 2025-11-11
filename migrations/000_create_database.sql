-- Migration 000: Create TWLan Database
-- Run this first to create the database

CREATE DATABASE IF NOT EXISTS twlan CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

USE twlan;

-- Verify database is created
SELECT 'Database twlan created successfully!' AS status;
