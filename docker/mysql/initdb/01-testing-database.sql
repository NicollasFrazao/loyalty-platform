-- The database the test suite migrates from scratch (phpunit.xml sets DB_DATABASE=loyalty_testing), so
-- `make test` never wipes the data of the running app. MySQL runs this once, when its volume is new.
CREATE DATABASE IF NOT EXISTS loyalty_testing CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
GRANT ALL PRIVILEGES ON loyalty_testing.* TO 'loyalty'@'%';
