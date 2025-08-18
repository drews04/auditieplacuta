/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;
DROP TABLE IF EXISTS `abilities`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `abilities` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `code` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `cooldown` int(11) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `abilities_code_unique` (`code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `cache`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `cache` (
  `key` varchar(255) NOT NULL,
  `value` mediumtext NOT NULL,
  `expiration` int(11) NOT NULL,
  PRIMARY KEY (`key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `cache_locks`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `cache_locks` (
  `key` varchar(255) NOT NULL,
  `owner` varchar(255) NOT NULL,
  `expiration` int(11) NOT NULL,
  PRIMARY KEY (`key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `competition_themes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `competition_themes` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `applies_on` date NOT NULL,
  `category_code` varchar(50) NOT NULL,
  `title` varchar(120) NOT NULL,
  `chosen_by` bigint(20) unsigned NOT NULL,
  `chosen_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `competition_themes_applies_on_unique` (`applies_on`),
  KEY `competition_themes_chosen_by_foreign` (`chosen_by`),
  CONSTRAINT `competition_themes_chosen_by_foreign` FOREIGN KEY (`chosen_by`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `failed_jobs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `failed_jobs` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `uuid` varchar(255) NOT NULL,
  `connection` text NOT NULL,
  `queue` text NOT NULL,
  `payload` longtext NOT NULL,
  `exception` longtext NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `job_batches`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `job_batches` (
  `id` varchar(255) NOT NULL,
  `name` varchar(255) NOT NULL,
  `total_jobs` int(11) NOT NULL,
  `pending_jobs` int(11) NOT NULL,
  `failed_jobs` int(11) NOT NULL,
  `failed_job_ids` longtext NOT NULL,
  `options` mediumtext DEFAULT NULL,
  `cancelled_at` int(11) DEFAULT NULL,
  `created_at` int(11) NOT NULL,
  `finished_at` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `jobs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `jobs` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `queue` varchar(255) NOT NULL,
  `payload` longtext NOT NULL,
  `attempts` tinyint(3) unsigned NOT NULL,
  `reserved_at` int(10) unsigned DEFAULT NULL,
  `available_at` int(10) unsigned NOT NULL,
  `created_at` int(10) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `jobs_queue_index` (`queue`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `migrations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `migrations` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `migration` varchar(255) NOT NULL,
  `batch` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `password_reset_tokens`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `password_reset_tokens` (
  `email` varchar(255) NOT NULL,
  `token` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `sessions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `sessions` (
  `id` varchar(255) NOT NULL,
  `user_id` bigint(20) unsigned DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `payload` longtext NOT NULL,
  `last_activity` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `sessions_user_id_index` (`user_id`),
  KEY `sessions_last_activity_index` (`last_activity`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `songs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `songs` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) unsigned NOT NULL,
  `youtube_url` varchar(255) NOT NULL,
  `title` varchar(255) DEFAULT NULL,
  `theme_id` bigint(20) unsigned DEFAULT NULL,
  `competition_date` date NOT NULL,
  `votes` int(11) NOT NULL DEFAULT 0,
  `is_winner` tinyint(1) NOT NULL DEFAULT 0,
  `winner_marked_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `songs_theme_id_foreign` (`theme_id`),
  KEY `songs_user_id_foreign` (`user_id`),
  CONSTRAINT `songs_theme_id_foreign` FOREIGN KEY (`theme_id`) REFERENCES `competition_themes` (`id`) ON DELETE SET NULL,
  CONSTRAINT `songs_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `themes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `themes` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `competition_date` date NOT NULL,
  `title` varchar(255) NOT NULL,
  `chosen_by_user_id` bigint(20) unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `themes_competition_date_unique` (`competition_date`),
  KEY `themes_chosen_by_user_id_foreign` (`chosen_by_user_id`),
  CONSTRAINT `themes_chosen_by_user_id_foreign` FOREIGN KEY (`chosen_by_user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `tiebreaks`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `tiebreaks` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `contest_date` date NOT NULL,
  `starts_at` datetime DEFAULT NULL,
  `ends_at` datetime DEFAULT NULL,
  `song_ids` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL CHECK (json_valid(`song_ids`)),
  `resolved` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `tiebreaks_contest_date_unique` (`contest_date`),
  KEY `tiebreaks_contest_date_index` (`contest_date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `user_abilities`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `user_abilities` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) unsigned NOT NULL,
  `ability_id` bigint(20) unsigned NOT NULL,
  `used_at` timestamp NULL DEFAULT NULL,
  `cooldown_ends_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `user_abilities_user_id_foreign` (`user_id`),
  KEY `user_abilities_ability_id_foreign` (`ability_id`),
  CONSTRAINT `user_abilities_ability_id_foreign` FOREIGN KEY (`ability_id`) REFERENCES `abilities` (`id`) ON DELETE CASCADE,
  CONSTRAINT `user_abilities_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `user_points`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `user_points` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) unsigned NOT NULL,
  `points` int(11) NOT NULL DEFAULT 0,
  `contest_date` date DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `song_id` bigint(20) unsigned DEFAULT NULL,
  `reason` varchar(32) NOT NULL DEFAULT 'position',
  `meta` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`meta`)),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uniq_user_day_reason` (`user_id`,`contest_date`,`reason`),
  KEY `user_points_song_id_foreign` (`song_id`),
  KEY `user_points_contest_date_index` (`contest_date`),
  KEY `user_points_reason_index` (`reason`),
  CONSTRAINT `user_points_song_id_foreign` FOREIGN KEY (`song_id`) REFERENCES `songs` (`id`) ON DELETE SET NULL,
  CONSTRAINT `user_points_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `users` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `remember_token` varchar(100) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `joined_at` timestamp NULL DEFAULT NULL,
  `wins` int(11) NOT NULL DEFAULT 0,
  `player_of_the_month` tinyint(1) NOT NULL DEFAULT 0,
  `player_of_the_year` tinyint(1) NOT NULL DEFAULT 0,
  `active_song_id` bigint(20) unsigned DEFAULT NULL,
  `trivia_wins` int(11) NOT NULL DEFAULT 0,
  `trivia_losses` int(11) NOT NULL DEFAULT 0,
  `missions_won` int(11) NOT NULL DEFAULT 0,
  `missions_lost` int(11) NOT NULL DEFAULT 0,
  `total_votes_received` int(11) NOT NULL DEFAULT 0,
  `total_votes_given` int(11) NOT NULL DEFAULT 0,
  `contest_entries` int(11) NOT NULL DEFAULT 0,
  `contest_wins` int(11) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  UNIQUE KEY `users_email_unique` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `v_daily_positions`;
/*!50001 DROP VIEW IF EXISTS `v_daily_positions`*/;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
/*!50001 CREATE VIEW `v_daily_positions` AS SELECT
 1 AS `song_id`,
  1 AS `user_id`,
  1 AS `contest_date`,
  1 AS `votes`,
  1 AS `created_at`,
  1 AS `position` */;
SET character_set_client = @saved_cs_client;
DROP TABLE IF EXISTS `v_user_personal_stats`;
/*!50001 DROP VIEW IF EXISTS `v_user_personal_stats`*/;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
/*!50001 CREATE VIEW `v_user_personal_stats` AS SELECT
 1 AS `user_id`,
  1 AS `participations`,
  1 AS `wins`,
  1 AS `votes_made`,
  1 AS `votes_received`,
  1 AS `last_win_song_id`,
  1 AS `last_win_song_title`,
  1 AS `last_win_date`,
  1 AS `last_win_theme_name` */;
SET character_set_client = @saved_cs_client;
DROP TABLE IF EXISTS `v_user_points_totals`;
/*!50001 DROP VIEW IF EXISTS `v_user_points_totals`*/;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
/*!50001 CREATE VIEW `v_user_points_totals` AS SELECT
 1 AS `user_id`,
  1 AS `all_time_points`,
  1 AS `year_points` */;
SET character_set_client = @saved_cs_client;
DROP TABLE IF EXISTS `v_user_stats_alltime`;
/*!50001 DROP VIEW IF EXISTS `v_user_stats_alltime`*/;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
/*!50001 CREATE VIEW `v_user_stats_alltime` AS SELECT
 1 AS `user_id`,
  1 AS `participations`,
  1 AS `wins`,
  1 AS `votes_received`,
  1 AS `votes_given` */;
SET character_set_client = @saved_cs_client;
DROP TABLE IF EXISTS `v_user_stats_monthly`;
/*!50001 DROP VIEW IF EXISTS `v_user_stats_monthly`*/;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
/*!50001 CREATE VIEW `v_user_stats_monthly` AS SELECT
 1 AS `user_id`,
  1 AS `ym`,
  1 AS `participations`,
  1 AS `wins`,
  1 AS `votes_received`,
  1 AS `votes_given` */;
SET character_set_client = @saved_cs_client;
DROP TABLE IF EXISTS `v_user_stats_weekly`;
/*!50001 DROP VIEW IF EXISTS `v_user_stats_weekly`*/;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
/*!50001 CREATE VIEW `v_user_stats_weekly` AS SELECT
 1 AS `user_id`,
  1 AS `yw`,
  1 AS `participations`,
  1 AS `wins`,
  1 AS `votes_received`,
  1 AS `votes_given` */;
SET character_set_client = @saved_cs_client;
DROP TABLE IF EXISTS `v_user_stats_yearly`;
/*!50001 DROP VIEW IF EXISTS `v_user_stats_yearly`*/;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
/*!50001 CREATE VIEW `v_user_stats_yearly` AS SELECT
 1 AS `user_id`,
  1 AS `y`,
  1 AS `participations`,
  1 AS `wins`,
  1 AS `votes_received`,
  1 AS `votes_given` */;
SET character_set_client = @saved_cs_client;
DROP TABLE IF EXISTS `votes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `votes` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) unsigned NOT NULL,
  `song_id` bigint(20) unsigned NOT NULL,
  `tiebreak_id` bigint(20) unsigned DEFAULT NULL,
  `vote_date` date NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `votes_user_id_vote_date_tiebreak_id_unique` (`user_id`,`vote_date`,`tiebreak_id`),
  KEY `votes_song_id_foreign` (`song_id`),
  KEY `votes_tiebreak_id_foreign` (`tiebreak_id`),
  CONSTRAINT `votes_song_id_foreign` FOREIGN KEY (`song_id`) REFERENCES `songs` (`id`) ON DELETE CASCADE,
  CONSTRAINT `votes_tiebreak_id_foreign` FOREIGN KEY (`tiebreak_id`) REFERENCES `tiebreaks` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `winners`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `winners` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `contest_date` date NOT NULL,
  `win_date` date DEFAULT NULL,
  `user_id` bigint(20) unsigned NOT NULL,
  `song_id` bigint(20) unsigned NOT NULL,
  `competition_theme_id` bigint(20) unsigned DEFAULT NULL,
  `vote_count` int(11) NOT NULL,
  `was_tie` tinyint(1) NOT NULL DEFAULT 0,
  `theme_chosen` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `winners_contest_date_unique` (`contest_date`),
  KEY `winners_user_id_foreign` (`user_id`),
  KEY `winners_song_id_foreign` (`song_id`),
  KEY `winners_competition_theme_id_fk` (`competition_theme_id`),
  CONSTRAINT `winners_competition_theme_id_fk` FOREIGN KEY (`competition_theme_id`) REFERENCES `competition_themes` (`id`) ON DELETE SET NULL,
  CONSTRAINT `winners_song_id_foreign` FOREIGN KEY (`song_id`) REFERENCES `songs` (`id`) ON DELETE CASCADE,
  CONSTRAINT `winners_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!50001 DROP VIEW IF EXISTS `v_daily_positions`*/;
/*!50001 SET @saved_cs_client          = @@character_set_client */;
/*!50001 SET @saved_cs_results         = @@character_set_results */;
/*!50001 SET @saved_col_connection     = @@collation_connection */;
/*!50001 SET character_set_client      = utf8mb4 */;
/*!50001 SET character_set_results     = utf8mb4 */;
/*!50001 SET collation_connection      = utf8mb4_unicode_ci */;
/*!50001 CREATE ALGORITHM=UNDEFINED */
/*!50013 DEFINER=`root`@`localhost` SQL SECURITY DEFINER */
/*!50001 VIEW `v_daily_positions` AS select `s`.`id` AS `song_id`,`s`.`user_id` AS `user_id`,`s`.`competition_date` AS `contest_date`,`s`.`votes` AS `votes`,`s`.`created_at` AS `created_at`,row_number() over ( partition by `s`.`competition_date` order by case when `w`.`song_id` is not null then 1 else 0 end desc,`s`.`votes` desc,`s`.`created_at`) AS `position` from (`songs` `s` left join `winners` `w` on(`w`.`contest_date` = `s`.`competition_date` and `w`.`song_id` = `s`.`id`)) */;
/*!50001 SET character_set_client      = @saved_cs_client */;
/*!50001 SET character_set_results     = @saved_cs_results */;
/*!50001 SET collation_connection      = @saved_col_connection */;
/*!50001 DROP VIEW IF EXISTS `v_user_personal_stats`*/;
/*!50001 SET @saved_cs_client          = @@character_set_client */;
/*!50001 SET @saved_cs_results         = @@character_set_results */;
/*!50001 SET @saved_col_connection     = @@collation_connection */;
/*!50001 SET character_set_client      = utf8mb4 */;
/*!50001 SET character_set_results     = utf8mb4 */;
/*!50001 SET collation_connection      = utf8mb4_unicode_ci */;
/*!50001 CREATE ALGORITHM=UNDEFINED */
/*!50013 DEFINER=`root`@`localhost` SQL SECURITY DEFINER */
/*!50001 VIEW `v_user_personal_stats` AS select `u`.`id` AS `user_id`,coalesce(`p`.`participations`,0) AS `participations`,coalesce(`wc`.`wins`,0) AS `wins`,coalesce(`vm`.`votes_made`,0) AS `votes_made`,coalesce(`vr`.`votes_received`,0) AS `votes_received`,`lw`.`song_id` AS `last_win_song_id`,`lw`.`song_title` AS `last_win_song_title`,`lw`.`won_on` AS `last_win_date`,`lw`.`theme_name` AS `last_win_theme_name` from (((((`users` `u` left join (select `s`.`user_id` AS `user_id`,count(0) AS `participations` from `songs` `s` group by `s`.`user_id`) `p` on(`p`.`user_id` = `u`.`id`)) left join (select `w`.`user_id` AS `user_id`,count(0) AS `wins` from `winners` `w` group by `w`.`user_id`) `wc` on(`wc`.`user_id` = `u`.`id`)) left join (select `v`.`user_id` AS `user_id`,count(0) AS `votes_made` from `votes` `v` group by `v`.`user_id`) `vm` on(`vm`.`user_id` = `u`.`id`)) left join (select `s`.`user_id` AS `user_id`,count(0) AS `votes_received` from (`votes` `v` join `songs` `s` on(`s`.`id` = `v`.`song_id`)) group by `s`.`user_id`) `vr` on(`vr`.`user_id` = `u`.`id`)) left join (select `x`.`user_id` AS `user_id`,`x`.`song_id` AS `song_id`,`x`.`song_title` AS `song_title`,`x`.`won_on` AS `won_on`,`x`.`theme_name` AS `theme_name` from (select `w1`.`user_id` AS `user_id`,`w1`.`song_id` AS `song_id`,cast(coalesce(`w1`.`contest_date`,`w1`.`created_at`) as date) AS `won_on`,`s`.`title` AS `song_title`,`ct`.`title` AS `theme_name`,row_number() over ( partition by `w1`.`user_id` order by cast(coalesce(`w1`.`contest_date`,`w1`.`created_at`) as date) desc,`w1`.`id` desc) AS `rn` from ((`winners` `w1` join `songs` `s` on(`s`.`id` = `w1`.`song_id`)) left join `competition_themes` `ct` on(`ct`.`id` = `s`.`theme_id`))) `x` where `x`.`rn` = 1) `lw` on(`lw`.`user_id` = `u`.`id`)) */;
/*!50001 SET character_set_client      = @saved_cs_client */;
/*!50001 SET character_set_results     = @saved_cs_results */;
/*!50001 SET collation_connection      = @saved_col_connection */;
/*!50001 DROP VIEW IF EXISTS `v_user_points_totals`*/;
/*!50001 SET @saved_cs_client          = @@character_set_client */;
/*!50001 SET @saved_cs_results         = @@character_set_results */;
/*!50001 SET @saved_col_connection     = @@collation_connection */;
/*!50001 SET character_set_client      = utf8mb4 */;
/*!50001 SET character_set_results     = utf8mb4 */;
/*!50001 SET collation_connection      = utf8mb4_unicode_ci */;
/*!50001 CREATE ALGORITHM=UNDEFINED */
/*!50013 DEFINER=`root`@`localhost` SQL SECURITY DEFINER */
/*!50001 VIEW `v_user_points_totals` AS select `u`.`id` AS `user_id`,coalesce(sum(`up`.`points`),0) AS `all_time_points`,coalesce(sum(case when `up`.`contest_date` is not null and year(`up`.`contest_date`) = year(curdate()) then `up`.`points` else 0 end),0) AS `year_points` from (`users` `u` left join `user_points` `up` on(`up`.`user_id` = `u`.`id`)) group by `u`.`id` */;
/*!50001 SET character_set_client      = @saved_cs_client */;
/*!50001 SET character_set_results     = @saved_cs_results */;
/*!50001 SET collation_connection      = @saved_col_connection */;
/*!50001 DROP VIEW IF EXISTS `v_user_stats_alltime`*/;
/*!50001 SET @saved_cs_client          = @@character_set_client */;
/*!50001 SET @saved_cs_results         = @@character_set_results */;
/*!50001 SET @saved_col_connection     = @@collation_connection */;
/*!50001 SET character_set_client      = utf8mb4 */;
/*!50001 SET character_set_results     = utf8mb4 */;
/*!50001 SET collation_connection      = utf8mb4_unicode_ci */;
/*!50001 CREATE ALGORITHM=UNDEFINED */
/*!50013 DEFINER=`root`@`localhost` SQL SECURITY DEFINER */
/*!50001 VIEW `v_user_stats_alltime` AS select `u`.`id` AS `user_id`,ifnull(`p`.`participations`,0) AS `participations`,ifnull(`w`.`wins`,0) AS `wins`,ifnull(`vr`.`votes_received`,0) AS `votes_received`,ifnull(`vg`.`votes_given`,0) AS `votes_given` from ((((`users` `u` left join (select `s`.`user_id` AS `user_id`,count(distinct `s`.`competition_date`) AS `participations` from `songs` `s` group by `s`.`user_id`) `p` on(`p`.`user_id` = `u`.`id`)) left join (select `w`.`user_id` AS `user_id`,count(0) AS `wins` from `winners` `w` group by `w`.`user_id`) `w` on(`w`.`user_id` = `u`.`id`)) left join (select `s`.`user_id` AS `user_id`,count(`v`.`id`) AS `votes_received` from (`votes` `v` join `songs` `s` on(`s`.`id` = `v`.`song_id`)) group by `s`.`user_id`) `vr` on(`vr`.`user_id` = `u`.`id`)) left join (select `v`.`user_id` AS `user_id`,count(`v`.`id`) AS `votes_given` from `votes` `v` group by `v`.`user_id`) `vg` on(`vg`.`user_id` = `u`.`id`)) */;
/*!50001 SET character_set_client      = @saved_cs_client */;
/*!50001 SET character_set_results     = @saved_cs_results */;
/*!50001 SET collation_connection      = @saved_col_connection */;
/*!50001 DROP VIEW IF EXISTS `v_user_stats_monthly`*/;
/*!50001 SET @saved_cs_client          = @@character_set_client */;
/*!50001 SET @saved_cs_results         = @@character_set_results */;
/*!50001 SET @saved_col_connection     = @@collation_connection */;
/*!50001 SET character_set_client      = utf8mb4 */;
/*!50001 SET character_set_results     = utf8mb4 */;
/*!50001 SET collation_connection      = utf8mb4_unicode_ci */;
/*!50001 CREATE ALGORITHM=UNDEFINED */
/*!50013 DEFINER=`root`@`localhost` SQL SECURITY DEFINER */
/*!50001 VIEW `v_user_stats_monthly` AS select `u`.`id` AS `user_id`,`cal`.`ym` AS `ym`,ifnull(`p`.`participations`,0) AS `participations`,ifnull(`w`.`wins`,0) AS `wins`,ifnull(`vr`.`votes_received`,0) AS `votes_received`,ifnull(`vg`.`votes_given`,0) AS `votes_given` from (((((`users` `u` join (select `m`.`ym` AS `ym` from (select distinct date_format(`s`.`competition_date`,'%Y-%m') AS `ym` from `songs` `s` union select distinct date_format(`w`.`contest_date`,'%Y-%m') AS `ym` from `winners` `w` union select distinct date_format(`s2`.`competition_date`,'%Y-%m') AS `ym` from (`votes` `v2` join `songs` `s2` on(`s2`.`id` = `v2`.`song_id`))) `m`) `cal` on(1 = 1)) left join (select `s`.`user_id` AS `user_id`,date_format(`s`.`competition_date`,'%Y-%m') AS `ym`,count(distinct `s`.`competition_date`) AS `participations` from `songs` `s` group by `s`.`user_id`,date_format(`s`.`competition_date`,'%Y-%m')) `p` on(`p`.`user_id` = `u`.`id` and `p`.`ym` = `cal`.`ym`)) left join (select `w`.`user_id` AS `user_id`,date_format(`w`.`contest_date`,'%Y-%m') AS `ym`,count(0) AS `wins` from `winners` `w` group by `w`.`user_id`,date_format(`w`.`contest_date`,'%Y-%m')) `w` on(`w`.`user_id` = `u`.`id` and `w`.`ym` = `cal`.`ym`)) left join (select `s`.`user_id` AS `user_id`,date_format(`s`.`competition_date`,'%Y-%m') AS `ym`,count(`v`.`id`) AS `votes_received` from (`votes` `v` join `songs` `s` on(`s`.`id` = `v`.`song_id`)) group by `s`.`user_id`,date_format(`s`.`competition_date`,'%Y-%m')) `vr` on(`vr`.`user_id` = `u`.`id` and `vr`.`ym` = `cal`.`ym`)) left join (select `v`.`user_id` AS `user_id`,date_format(`s`.`competition_date`,'%Y-%m') AS `ym`,count(`v`.`id`) AS `votes_given` from (`votes` `v` join `songs` `s` on(`s`.`id` = `v`.`song_id`)) group by `v`.`user_id`,date_format(`s`.`competition_date`,'%Y-%m')) `vg` on(`vg`.`user_id` = `u`.`id` and `vg`.`ym` = `cal`.`ym`)) order by `u`.`id`,`cal`.`ym` */;
/*!50001 SET character_set_client      = @saved_cs_client */;
/*!50001 SET character_set_results     = @saved_cs_results */;
/*!50001 SET collation_connection      = @saved_col_connection */;
/*!50001 DROP VIEW IF EXISTS `v_user_stats_weekly`*/;
/*!50001 SET @saved_cs_client          = @@character_set_client */;
/*!50001 SET @saved_cs_results         = @@character_set_results */;
/*!50001 SET @saved_col_connection     = @@collation_connection */;
/*!50001 SET character_set_client      = utf8mb4 */;
/*!50001 SET character_set_results     = utf8mb4 */;
/*!50001 SET collation_connection      = utf8mb4_unicode_ci */;
/*!50001 CREATE ALGORITHM=UNDEFINED */
/*!50013 DEFINER=`root`@`localhost` SQL SECURITY DEFINER */
/*!50001 VIEW `v_user_stats_weekly` AS select `base`.`user_id` AS `user_id`,`base`.`yw` AS `yw`,coalesce(`p`.`participations`,0) AS `participations`,coalesce(`w`.`wins`,0) AS `wins`,coalesce(`vr`.`votes_received`,0) AS `votes_received`,coalesce(`vg`.`votes_given`,0) AS `votes_given` from (((((select `s`.`user_id` AS `user_id`,yearweek(`s`.`competition_date`,1) AS `yw` from `songs` `s` union select `w`.`user_id` AS `user_id`,yearweek(`w`.`contest_date`,1) AS `yw` from `winners` `w` union select `s`.`user_id` AS `user_id`,yearweek(`s`.`competition_date`,1) AS `yw` from (`votes` `v` join `songs` `s` on(`s`.`id` = `v`.`song_id`))) `base` left join (select `s`.`user_id` AS `user_id`,yearweek(`s`.`competition_date`,1) AS `yw`,count(distinct `s`.`competition_date`) AS `participations` from `songs` `s` group by `s`.`user_id`,yearweek(`s`.`competition_date`,1)) `p` on(`p`.`user_id` = `base`.`user_id` and `p`.`yw` = `base`.`yw`)) left join (select `w`.`user_id` AS `user_id`,yearweek(`w`.`contest_date`,1) AS `yw`,count(0) AS `wins` from `winners` `w` group by `w`.`user_id`,yearweek(`w`.`contest_date`,1)) `w` on(`w`.`user_id` = `base`.`user_id` and `w`.`yw` = `base`.`yw`)) left join (select `s`.`user_id` AS `user_id`,yearweek(`s`.`competition_date`,1) AS `yw`,count(`v`.`id`) AS `votes_received` from (`votes` `v` join `songs` `s` on(`s`.`id` = `v`.`song_id`)) group by `s`.`user_id`,yearweek(`s`.`competition_date`,1)) `vr` on(`vr`.`user_id` = `base`.`user_id` and `vr`.`yw` = `base`.`yw`)) left join (select `v`.`user_id` AS `user_id`,yearweek(`s`.`competition_date`,1) AS `yw`,count(`v`.`id`) AS `votes_given` from (`votes` `v` join `songs` `s` on(`s`.`id` = `v`.`song_id`)) group by `v`.`user_id`,yearweek(`s`.`competition_date`,1)) `vg` on(`vg`.`user_id` = `base`.`user_id` and `vg`.`yw` = `base`.`yw`)) */;
/*!50001 SET character_set_client      = @saved_cs_client */;
/*!50001 SET character_set_results     = @saved_cs_results */;
/*!50001 SET collation_connection      = @saved_col_connection */;
/*!50001 DROP VIEW IF EXISTS `v_user_stats_yearly`*/;
/*!50001 SET @saved_cs_client          = @@character_set_client */;
/*!50001 SET @saved_cs_results         = @@character_set_results */;
/*!50001 SET @saved_col_connection     = @@collation_connection */;
/*!50001 SET character_set_client      = utf8mb4 */;
/*!50001 SET character_set_results     = utf8mb4 */;
/*!50001 SET collation_connection      = utf8mb4_unicode_ci */;
/*!50001 CREATE ALGORITHM=UNDEFINED */
/*!50013 DEFINER=`root`@`localhost` SQL SECURITY DEFINER */
/*!50001 VIEW `v_user_stats_yearly` AS select `u`.`id` AS `user_id`,`cal`.`y` AS `y`,ifnull(`p`.`participations`,0) AS `participations`,ifnull(`w`.`wins`,0) AS `wins`,ifnull(`vr`.`votes_received`,0) AS `votes_received`,ifnull(`vg`.`votes_given`,0) AS `votes_given` from (((((`users` `u` join (select `yy`.`y` AS `y` from (select distinct year(`s`.`competition_date`) AS `y` from `songs` `s` union select distinct year(`w`.`contest_date`) AS `y` from `winners` `w` union select distinct year(`s2`.`competition_date`) AS `y` from (`votes` `v2` join `songs` `s2` on(`s2`.`id` = `v2`.`song_id`))) `yy`) `cal` on(1 = 1)) left join (select `s`.`user_id` AS `user_id`,year(`s`.`competition_date`) AS `y`,count(distinct `s`.`competition_date`) AS `participations` from `songs` `s` group by `s`.`user_id`,year(`s`.`competition_date`)) `p` on(`p`.`user_id` = `u`.`id` and `p`.`y` = `cal`.`y`)) left join (select `w`.`user_id` AS `user_id`,year(`w`.`contest_date`) AS `y`,count(0) AS `wins` from `winners` `w` group by `w`.`user_id`,year(`w`.`contest_date`)) `w` on(`w`.`user_id` = `u`.`id` and `w`.`y` = `cal`.`y`)) left join (select `s`.`user_id` AS `user_id`,year(`s`.`competition_date`) AS `y`,count(`v`.`id`) AS `votes_received` from (`votes` `v` join `songs` `s` on(`s`.`id` = `v`.`song_id`)) group by `s`.`user_id`,year(`s`.`competition_date`)) `vr` on(`vr`.`user_id` = `u`.`id` and `vr`.`y` = `cal`.`y`)) left join (select `v`.`user_id` AS `user_id`,year(`s`.`competition_date`) AS `y`,count(`v`.`id`) AS `votes_given` from (`votes` `v` join `songs` `s` on(`s`.`id` = `v`.`song_id`)) group by `v`.`user_id`,year(`s`.`competition_date`)) `vg` on(`vg`.`user_id` = `u`.`id` and `vg`.`y` = `cal`.`y`)) order by `u`.`id`,`cal`.`y` */;
/*!50001 SET character_set_client      = @saved_cs_client */;
/*!50001 SET character_set_results     = @saved_cs_results */;
/*!50001 SET collation_connection      = @saved_col_connection */;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (1,'0001_01_01_000000_create_users_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (2,'0001_01_01_000001_create_cache_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (3,'0001_01_01_000002_create_jobs_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (4,'2025_08_05_005502_add_profile_fields_to_users_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (5,'2025_08_05_011006_add_game_stats_to_users_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (6,'2025_08_06_072247_create_abilities_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (7,'2025_08_06_072248_create_user_abilities_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (8,'2025_08_06_094800_create_competition_themes_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (9,'2025_08_06_094853_create_songs_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (10,'2025_08_06_100212_create_votes_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (11,'2025_08_07_090932_create_themes_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (12,'2025_08_07_092414_add_winner_timestamp_to_songs_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (13,'2025_08_07_110220_create_winners_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (14,'2025_08_12_094935_create_tiebreaks_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (15,'2025_08_12_104942_alter_votes_for_tiebreak',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (16,'2025_08_13_113142_create_user_stats_views_from_songs',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (17,'2025_08_14_082118_create_view_v_user_stats_alltime',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (18,'2025_08_14_083559_create_view_v_user_stats_weekly',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (19,'2025_08_14_122237_create_view_v_user_personal_stats',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (20,'2025_08_14_125732_update_view_v_user_personal_stats_add_theme',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (21,'2025_08_14_130824_fix_view_v_user_personal_stats',2);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (22,'2025_08_14_144053_add_competition_theme_id_to_winners_table',2);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (23,'2025_08_14_165637_create_user_points_table',3);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (24,'2025_08_15_100539_create_view_v_daily_positions',4);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (25,'2025_08_16_134419_add_win_date_to_winners_table',5);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (26,'2025_08_18_121137_alter_user_points_add_ledger_columns',6);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (27,'2025_08_18_122908_create_view_v_user_points_totals',7);
