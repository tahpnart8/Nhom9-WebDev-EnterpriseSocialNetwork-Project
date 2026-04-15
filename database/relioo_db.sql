-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 100.72.177.39
-- Generation Time: Apr 15, 2026 at 09:22 AM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.0.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `relioo_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `comments`
--

CREATE TABLE `comments` (
  `id` int(11) NOT NULL,
  `post_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `parent_comment_id` int(11) DEFAULT NULL,
  `content` text NOT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `comments`
--

INSERT INTO `comments` (`id`, `post_id`, `user_id`, `parent_comment_id`, `content`, `created_at`) VALUES
(1, 1, 1, NULL, 'đasads', '2026-04-09 09:51:38'),
(2, 1, 3, NULL, 'heheádasd', '2026-04-09 09:51:53'),
(3, 1, 3, 1, 'dạ', '2026-04-09 09:52:35'),
(4, 2, 1, NULL, 'rfftftft haha', '2026-04-11 00:23:26'),
(6, 3, 3, NULL, 'jojdjawkdkwaldkwadmkwadkwamd', '2026-04-11 03:51:04'),
(7, 3, 3, NULL, 'jojdjawkdkwaldkwadmkwadkwamdda', '2026-04-11 03:51:04'),
(8, 3, 3, NULL, 'd', '2026-04-11 03:51:05'),
(9, 3, 3, NULL, 'dda', '2026-04-11 03:51:06'),
(10, 3, 3, NULL, 'ád', '2026-04-11 03:51:06'),
(11, 3, 3, NULL, 'ádd', '2026-04-11 03:51:07'),
(12, 3, 3, NULL, 'ádds', '2026-04-11 03:51:07'),
(13, 3, 3, NULL, 'áddsad', '2026-04-11 03:51:08'),
(14, 3, 3, NULL, 'áddsadsad', '2026-04-11 03:51:08'),
(15, 3, 3, NULL, 'áddsadsad', '2026-04-11 03:51:09'),
(16, 3, 3, NULL, 'áddsadsad', '2026-04-11 03:51:10'),
(17, 3, 3, NULL, 'á', '2026-04-11 03:51:10'),
(18, 3, 3, NULL, 'áds', '2026-04-11 03:51:10'),
(19, 3, 3, NULL, 'd', '2026-04-11 03:51:10'),
(20, 3, 3, NULL, 'dsd', '2026-04-11 03:51:10'),
(21, 3, 3, NULL, 'dsdá', '2026-04-11 03:51:11'),
(22, 3, 3, NULL, 'a', '2026-04-11 03:51:11'),
(23, 3, 3, NULL, 'd', '2026-04-11 03:51:11'),
(24, 3, 3, NULL, 'á', '2026-04-11 03:51:11'),
(25, 3, 3, NULL, 'ád', '2026-04-11 03:51:11'),
(26, 3, 3, NULL, 'áds', '2026-04-11 03:51:11'),
(27, 3, 3, NULL, 'ádsd', '2026-04-11 03:51:12'),
(28, 3, 3, NULL, 'ádsdad', '2026-04-11 03:51:12'),
(29, 3, 3, NULL, 'ádsdad', '2026-04-11 03:51:12'),
(30, 3, 3, NULL, 'ádsdada', '2026-04-11 03:51:12'),
(31, 3, 3, NULL, 'd', '2026-04-11 03:51:13'),
(32, 3, 3, NULL, 'dá', '2026-04-11 03:51:13'),
(33, 3, 3, NULL, 'dáda', '2026-04-11 03:51:13'),
(34, 3, 3, NULL, 'da', '2026-04-11 03:51:13'),
(35, 4, 2, NULL, 'ok', '2026-04-11 09:56:05'),
(37, 11, 1, NULL, 'Đẹp trai thế', '2026-04-11 13:40:14'),
(38, 11, 1, NULL, 'ẢNH PHẢN CẢM QUÁ, XÓA DÙM', '2026-04-11 13:40:18'),
(39, 10, 3, NULL, 'ngu quá xuống chức đi', '2026-04-11 13:46:13'),
(40, 10, 2, 39, 'ư t kick m bây g dám chửi sếp', '2026-04-11 15:28:29'),
(41, 20, 3, NULL, 'Cho 5 suất bánh tráng tôm', '2026-04-12 21:19:44'),
(42, 20, 3, 41, 'Kèm hành phi', '2026-04-12 21:21:02'),
(43, 20, 3, NULL, 'Cho một cân ruốc đi thằng ngu', '2026-04-12 23:22:46'),
(44, 11, 2, NULL, 'Tuyệt cà là vời, em là đẹp nhất', '2026-04-12 23:37:36'),
(46, 19, 3, NULL, 'Chúc chúng ta luôn tiến bước như vậy', '2026-04-12 23:50:09'),
(48, 3, 2, NULL, 'Công ty sắp phá sản r em', '2026-04-12 23:58:13'),
(50, 19, 3, NULL, 'Dạaaa', '2026-04-13 00:07:50'),
(52, 19, 2, NULL, 'hello mấy cưng', '2026-04-13 00:42:36'),
(53, 11, 2, NULL, 'Đẹp trai, số 1', '2026-04-13 00:44:47'),
(54, 11, 2, NULL, 'very good', '2026-04-13 00:44:57'),
(55, 11, 3, 54, 'cảm ơn anh', '2026-04-13 00:48:03'),
(56, 20, 3, NULL, 'Thèm quá đi', '2026-04-13 00:55:13'),
(57, 20, 2, 56, 'ok', '2026-04-13 00:56:20'),
(58, 22, 3, NULL, 'mệt vcl', '2026-04-14 16:08:59');

-- --------------------------------------------------------

--
-- Table structure for table `comment_reactions`
--

CREATE TABLE `comment_reactions` (
  `id` int(11) NOT NULL,
  `comment_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `comment_reactions`
--

INSERT INTO `comment_reactions` (`id`, `comment_id`, `user_id`, `created_at`) VALUES
(1, 1, 3, '2026-04-09 09:51:44'),
(2, 4, 1, '2026-04-11 00:23:29');

-- --------------------------------------------------------

--
-- Table structure for table `conversations`
--

CREATE TABLE `conversations` (
  `id` int(11) NOT NULL,
  `type` enum('Direct','Group') DEFAULT 'Direct',
  `name` varchar(100) DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `avatar_url` varchar(255) DEFAULT NULL,
  `created_by` int(11) DEFAULT NULL,
  `requires_approval` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `conversations`
--

INSERT INTO `conversations` (`id`, `type`, `name`, `created_at`, `avatar_url`, `created_by`, `requires_approval`) VALUES
(1, 'Direct', NULL, '2026-04-06 19:42:50', NULL, NULL, 0),
(2, 'Direct', NULL, '2026-04-09 12:02:29', NULL, NULL, 0),
(3, 'Direct', NULL, '2026-04-11 12:00:20', NULL, NULL, 0),
(4, 'Direct', NULL, '2026-04-11 12:00:31', NULL, NULL, 0),
(5, 'Group', 'Hội bạn thân IT ngu', '2026-04-13 02:02:09', 'https://i.ibb.co/HpzhRX8Z/8275ab63446d.jpg', 3, 0);

-- --------------------------------------------------------

--
-- Table structure for table `conversation_members`
--

CREATE TABLE `conversation_members` (
  `conversation_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `last_read_at` datetime DEFAULT current_timestamp(),
  `role` enum('admin','member') DEFAULT 'member'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `conversation_members`
--

INSERT INTO `conversation_members` (`conversation_id`, `user_id`, `last_read_at`, `role`) VALUES
(1, 3, '2026-04-15 10:12:56', 'member'),
(1, 4, '2026-04-12 23:04:34', 'member'),
(2, 2, '2026-04-15 10:16:46', 'member'),
(2, 3, '2026-04-15 10:15:58', 'member'),
(3, 1, '2026-04-15 00:37:40', 'member'),
(3, 2, '2026-04-15 10:14:32', 'member'),
(4, 1, '2026-04-15 00:38:05', 'member'),
(4, 3, '2026-04-15 10:16:00', 'member'),
(5, 1, '2026-04-15 10:22:45', 'member'),
(5, 2, '2026-04-15 10:14:26', 'member'),
(5, 3, '2026-04-15 14:17:47', 'admin');

-- --------------------------------------------------------

--
-- Table structure for table `departments`
--

CREATE TABLE `departments` (
  `id` int(11) NOT NULL,
  `dept_name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `departments`
--

INSERT INTO `departments` (`id`, `dept_name`, `description`, `created_at`) VALUES
(1, 'Ban Giám Đốc', NULL, '2026-04-08 22:54:32'),
(2, 'Kinh Doanh', NULL, '2026-04-08 22:54:32'),
(3, 'Kỹ Thuật', NULL, '2026-04-08 22:54:32');

-- --------------------------------------------------------

--
-- Table structure for table `membership_requests`
--

CREATE TABLE `membership_requests` (
  `id` int(11) NOT NULL,
  `conversation_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `invited_by` int(11) NOT NULL,
  `status` enum('pending','approved','rejected') DEFAULT 'pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `messages`
--

CREATE TABLE `messages` (
  `id` int(11) NOT NULL,
  `conversation_id` int(11) NOT NULL,
  `sender_id` int(11) NOT NULL,
  `content` text NOT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `messages`
--

INSERT INTO `messages` (`id`, `conversation_id`, `sender_id`, `content`, `created_at`) VALUES
(1, 2, 3, 'Hi', '2026-04-09 12:02:32'),
(2, 2, 2, 'hi nhu', '2026-04-11 03:18:52'),
(3, 2, 3, 'efklefnokk //d.=/1=\' qe13', '2026-04-11 03:46:24'),
(4, 2, 3, 'jdjad.com', '2026-04-11 03:46:28'),
(5, 2, 2, 'long ngu', '2026-04-11 03:46:32'),
(6, 2, 2, 'ê', '2026-04-11 03:46:58'),
(7, 2, 2, 'biết gì ko', '2026-04-11 03:47:00'),
(8, 2, 2, 'lồn', '2026-04-11 03:47:16'),
(9, 2, 3, '......', '2026-04-11 03:47:16'),
(10, 3, 2, 't nghỉ việc', '2026-04-11 12:09:02'),
(11, 3, 1, 'kệ mẹ mày', '2026-04-11 13:37:46'),
(12, 2, 3, 'ngu rồi Long ơi', '2026-04-11 13:37:49'),
(13, 2, 3, 'fsf', '2026-04-11 13:37:49'),
(14, 2, 3, 'hi th ngu', '2026-04-11 13:37:51'),
(15, 4, 1, 'anh chào vũ', '2026-04-11 13:50:13'),
(16, 4, 1, 'theo cơ cấu nhân sự mới của công ty mình', '2026-04-11 13:50:22'),
(17, 4, 1, 'anh trân trọng thông báo em bị đuổi việc', '2026-04-11 13:50:34'),
(18, 4, 1, 'cám ơn em đã cống hiến cho công ty mình trong thời gian qua', '2026-04-11 13:51:19'),
(19, 4, 3, 'Dạ theo hợp đồng khi bên công ty đuổi em thì sẽ phải trả tiền chấm dứt hợp đồng ạ, anh đền nổi không', '2026-04-11 13:52:08'),
(20, 4, 1, '?', '2026-04-11 13:52:25'),
(21, 4, 3, 'Em có hình ảnh anh ngoại tình với Trần IT Trưởng, 100 triệu em sẽ em miệng', '2026-04-11 13:53:44'),
(22, 4, 1, '? nhưng anh là con trai mà em', '2026-04-11 13:54:12'),
(23, 4, 3, 'Nhưng em biết Trần IT Trưởng chưa chắc là con trai', '2026-04-11 13:54:58'),
(24, 4, 1, '@AI Tr Phát tôi muốn gửi ảnh cap màn hình nói xấu nhân viên !!', '2026-04-11 13:55:28'),
(25, 2, 2, 'lêu lêu th ngoo', '2026-04-12 15:15:35'),
(26, 4, 1, 'avatar đẹp v ku', '2026-04-12 15:58:29'),
(27, 3, 1, 'dạ a IT trần', '2026-04-12 15:58:50'),
(28, 3, 1, 'Cho t 1 bịch cơm cháy nha', '2026-04-12 16:02:25'),
(29, 3, 2, 'ngu', '2026-04-12 22:02:59'),
(30, 4, 3, 'ok ko sếp', '2026-04-12 23:11:15'),
(31, 2, 3, 'Sếp là đàn em t coi chừng t kêu đuổi việc m', '2026-04-12 23:11:35'),
(32, 2, 2, 'kệ mẹ m', '2026-04-12 23:12:36'),
(33, 2, 2, 'tôi còn là ông nội của sếp nè', '2026-04-12 23:14:23'),
(34, 2, 3, 'Thế t là ông cố tổ của sếp luôn', '2026-04-12 23:21:28'),
(35, 2, 3, 'oắt con', '2026-04-12 23:21:32'),
(36, 2, 3, 't nghỉ việc bây giờ', '2026-04-12 23:21:36'),
(37, 2, 3, 'lượng 3tr mà đòi t làm nhiều hả', '2026-04-12 23:21:45'),
(38, 2, 2, 'Ok m', '2026-04-12 23:36:43'),
(39, 2, 2, 'hèn gì mãi nghèo', '2026-04-12 23:36:50'),
(40, 2, 2, 'chỉ xứng đáng làm công nhân', '2026-04-12 23:36:57'),
(41, 4, 1, 'giỏi lắm con trai của ta', '2026-04-12 23:43:00'),
(42, 2, 3, 'sadsf', '2026-04-12 23:50:33'),
(43, 2, 3, 'sadsfdg', '2026-04-12 23:50:36'),
(44, 2, 3, 'addsfgf', '2026-04-12 23:50:37'),
(45, 2, 3, 'sdsfghjkhjgdhfsd', '2026-04-13 00:06:37'),
(46, 2, 3, 'zzdvxbcn', '2026-04-13 00:06:40'),
(47, 2, 3, 'zvxvbcnv', '2026-04-13 00:06:41'),
(48, 2, 3, 'dagsdgfadhdf', '2026-04-13 00:54:52'),
(49, 2, 3, 'sgdafhdfhaf', '2026-04-13 00:54:54'),
(50, 2, 3, 'shadffhfdh', '2026-04-13 00:54:56'),
(51, 2, 3, 'dfhadfhdgh', '2026-04-13 00:54:59'),
(52, 5, 3, 'Ê', '2026-04-13 02:02:26'),
(53, 5, 3, 't nói cho tụi m nghe', '2026-04-13 02:02:31'),
(54, 5, 2, 'cái gì', '2026-04-13 02:21:58'),
(55, 5, 2, 'ngu quá', '2026-04-13 02:22:03'),
(56, 5, 2, 'hahaa', '2026-04-13 02:22:21'),
(57, 5, 2, 'dạaaa', '2026-04-13 02:31:43'),
(58, 5, 2, 'hmmmmm', '2026-04-13 02:32:42'),
(59, 5, 2, 'hmmmmm', '2026-04-13 02:32:44'),
(60, 5, 1, 'nhắn nhắn cái cmm', '2026-04-13 09:44:00'),
(61, 5, 1, 'dạ sêos', '2026-04-14 01:25:02'),
(62, 2, 1, 'M đâu r', '2026-04-14 01:25:32'),
(63, 2, 3, 'ngủ r', '2026-04-14 01:28:42'),
(64, 5, 3, 'zsdfghjkl', '2026-04-14 01:34:30'),
(65, 5, 3, 'sdfghjkl', '2026-04-14 01:34:32'),
(66, 5, 3, 'fdghjkuilo', '2026-04-14 01:34:34'),
(67, 5, 3, 'fghjkl', '2026-04-14 01:34:35'),
(68, 5, 3, 'dẻtyuio', '2026-04-14 01:34:45'),
(69, 2, 3, 'dfghjk', '2026-04-14 01:35:01'),
(70, 2, 3, 'cdfvghjkl', '2026-04-14 01:35:03'),
(71, 2, 3, 'dfghjk', '2026-04-14 01:35:06'),
(72, 2, 2, 'xcfsbfgxgsgsgg', '2026-04-14 01:43:21'),
(73, 2, 2, 'vcxvxcvxcvvvx', '2026-04-14 01:43:48'),
(74, 2, 3, '👍', '2026-04-14 01:54:27'),
(75, 2, 3, '👍', '2026-04-14 01:55:48'),
(76, 2, 3, '👍', '2026-04-14 01:55:49'),
(77, 5, 3, '👍', '2026-04-14 01:56:11'),
(78, 5, 3, '👍', '2026-04-14 01:56:12'),
(79, 5, 3, '👍', '2026-04-14 01:56:13'),
(80, 5, 3, '👍', '2026-04-14 01:56:14'),
(81, 5, 3, '👍', '2026-04-14 01:56:28'),
(82, 5, 3, '👍', '2026-04-14 01:56:30'),
(83, 5, 3, '👍', '2026-04-14 01:56:31'),
(84, 2, 3, '👍', '2026-04-14 01:58:37'),
(85, 2, 3, '👍', '2026-04-14 01:58:38'),
(86, 2, 3, '👍', '2026-04-14 01:58:39'),
(87, 2, 3, 'XIN CHÀO MỌI NGƯỜI !!!🥳', '2026-04-14 02:02:29'),
(88, 2, 3, '[IMAGE:https://i.ibb.co/LzvL6YZ0/d36993503ac3.jpg]', '2026-04-14 02:02:44'),
(89, 5, 2, '👍', '2026-04-14 02:06:05'),
(90, 5, 3, 'XIN CHÀO MỌI NGƯỜI !!!🥳', '2026-04-14 02:11:16'),
(91, 5, 3, '[IMAGE:https://i.ibb.co/b5NKKjNw/cb5913c510f3.jpg]', '2026-04-14 02:11:57'),
(92, 5, 3, 'mai đi bonding', '2026-04-14 02:13:08'),
(93, 5, 3, 'ở Kiên Giang', '2026-04-14 02:13:17'),
(94, 5, 3, 'ok nha', '2026-04-14 02:13:20'),
(95, 5, 3, 'scdas', '2026-04-14 02:16:20'),
(96, 2, 2, 'ok m', '2026-04-14 02:25:32'),
(97, 2, 2, 'Cho m 1 tỷ', '2026-04-14 02:25:52'),
(98, 2, 3, 'Ok Sếp', '2026-04-14 15:33:33'),
(99, 2, 3, 'Đứa nào cầm acc sếp sủa cái', '2026-04-14 15:33:48'),
(100, 5, 1, 'ok', '2026-04-14 16:00:21'),
(101, 5, 1, 'mai đi bonding ở kiên giang', '2026-04-14 16:00:25'),
(102, 5, 3, 'ngu', '2026-04-14 16:07:51'),
(103, 5, 3, 'bonding cái lồn', '2026-04-14 16:07:55'),
(104, 5, 3, 'ngu', '2026-04-14 16:07:57'),
(105, 5, 3, '👍', '2026-04-14 16:08:02'),
(106, 2, 3, '👍', '2026-04-15 08:33:55'),
(107, 2, 2, 'ngu', '2026-04-15 10:12:52'),
(108, 2, 2, 'ngu', '2026-04-15 10:13:13'),
(109, 2, 2, 'ngu', '2026-04-15 10:13:23'),
(110, 2, 2, 'ngu', '2026-04-15 10:13:29'),
(111, 2, 2, 'ngu', '2026-04-15 10:13:38'),
(112, 2, 3, 'ê', '2026-04-15 10:13:46'),
(113, 2, 3, '👍', '2026-04-15 10:13:48'),
(114, 2, 3, '👍', '2026-04-15 10:13:48'),
(115, 2, 3, '👍', '2026-04-15 10:13:49'),
(116, 2, 3, '👍', '2026-04-15 10:13:49'),
(117, 5, 2, 'ngu', '2026-04-15 10:14:17'),
(118, 2, 2, 'ngu', '2026-04-15 10:14:23'),
(119, 3, 2, 'ngu', '2026-04-15 10:14:28'),
(120, 2, 2, 'ngu', '2026-04-15 10:14:35'),
(121, 2, 2, 'ádasdsadasd', '2026-04-15 10:14:41'),
(122, 5, 1, 'cái l gì d', '2026-04-15 10:22:06'),
(123, 5, 1, '?', '2026-04-15 10:22:10');

-- --------------------------------------------------------

--
-- Table structure for table `notifications`
--

CREATE TABLE `notifications` (
  `id` int(11) NOT NULL,
  `type` varchar(50) NOT NULL,
  `trigger_user_id` int(11) DEFAULT NULL,
  `content` text NOT NULL,
  `target_url` varchar(255) DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `notifications`
--

INSERT INTO `notifications` (`id`, `type`, `trigger_user_id`, `content`, `target_url`, `created_at`) VALUES
(1, 'task_approval', 3, 'Nhân viên Vũ Nhân Viên 1 đã gửi duyệt subtask: Thiết kế Giao diện Kanban', 'index.php?action=tasks', '2026-04-09 10:33:46'),
(2, 'task_rejected', 1, 'Subtask \'Thiết kế Giao diện Kanban\' bị TỪ CHỐI: gà', 'index.php?action=tasks', '2026-04-09 10:34:06'),
(3, 'task_approved', 2, 'Subtask \'Thiết kế Giao diện Kanban\' đã được DUYỆT và Hoàn thành!', 'index.php?action=tasks', '2026-04-09 12:08:11'),
(4, 'task_approval', 3, 'Nhân viên Vũ Nhân Viên 1 đã gửi duyệt subtask: Xây dựng API Phê duyệt', 'index.php?action=tasks', '2026-04-09 12:08:41'),
(5, 'task_rejected', 2, 'Subtask \'Xây dựng API Phê duyệt\' bị TỪ CHỐI: ngu haha', 'index.php?action=tasks', '2026-04-09 12:09:10'),
(6, 'task_approval', 3, 'Nhân viên Vũ Nhân Viên 1 đã gửi duyệt subtask: Xây dựng API Phê duyệt', 'index.php?action=tasks', '2026-04-09 12:09:25'),
(7, 'task_rejected', 2, 'Subtask \'Xây dựng API Phê duyệt\' bị TỪ CHỐI: làm lại đi', 'index.php?action=tasks', '2026-04-09 12:13:02'),
(8, 'task_approval', 3, 'Nhân viên Vũ Nhân Viên 1 đã gửi duyệt subtask: Xây dựng API Phê duyệt', 'index.php?action=tasks', '2026-04-09 12:13:18'),
(9, 'task_approved', 2, 'Subtask \'Xây dựng API Phê duyệt\' đã được DUYỆT và Hoàn thành!', 'index.php?action=tasks', '2026-04-09 12:17:24'),
(10, 'task_approval', 3, 'Nhân viên Vũ Nhân Viên 1 đã gửi duyệt subtask: làm lẹ mày', 'index.php?action=tasks', '2026-04-09 12:17:56'),
(11, 'task_approved', 2, 'Subtask \'làm lẹ mày\' đã được DUYỆT và Hoàn thành!', 'index.php?action=tasks', '2026-04-09 12:18:08'),
(12, 'task_approval', 3, 'Nhân viên Vũ Nhân Viên 1 đã gửi duyệt subtask: âcsc', 'index.php?action=tasks', '2026-04-11 01:28:44'),
(13, 'task_approval', 3, 'Nhân viên Vũ Nhân Viên 1 đã gửi duyệt subtask: ừ', 'index.php?action=tasks', '2026-04-11 02:21:21'),
(14, 'task_assigned', 1, 'Bạn được giao 1 việc mới trong Task: cxzzxc', 'index.php?action=tasks', '2026-04-11 02:33:54'),
(15, 'task_assigned', 1, 'Bạn được giao 1 việc mới trong Task: cxzzxc', 'index.php?action=tasks', '2026-04-11 02:33:56'),
(16, 'task_approval', 3, 'Nhân viên Vũ Nhân Viên 1 đã gửi duyệt subtask: ùa', 'index.php?action=tasks', '2026-04-11 02:34:19'),
(17, 'task_approved', 1, 'Subtask \'âcsc\' đã được DUYỆT và Hoàn thành!', 'index.php?action=tasks', '2026-04-11 02:34:56'),
(18, 'task_assigned', 2, 'Bạn được giao việc trong Task mới: THIẾT KẾ GIAO DIỆN RELIOO', 'index.php?action=tasks', '2026-04-11 02:53:55'),
(19, 'task_assigned', 2, 'Bạn được giao việc trong Task mới: THIẾT KẾ GIAO DIỆN RELIOO', 'index.php?action=tasks', '2026-04-11 02:53:55'),
(20, 'task_assigned', 2, 'Bạn được giao việc trong Task mới: THIẾT KẾ GIAO DIỆN RELIOO', 'index.php?action=tasks', '2026-04-11 02:53:55'),
(21, 'task_assigned', 2, 'Bạn được giao việc trong Task mới: THIẾT KẾ GIAO DIỆN RELIOO', 'index.php?action=tasks', '2026-04-11 02:53:56'),
(22, 'task_assigned', 2, 'Bạn được giao việc trong Task mới: THIẾT KẾ GIAO DIỆN RELIOO', 'index.php?action=tasks', '2026-04-11 02:53:56'),
(23, 'task_assigned', 2, 'Bạn được giao việc trong Task mới: THIẾT KẾ GIAO DIỆN RELIOO', 'index.php?action=tasks', '2026-04-11 02:53:56'),
(24, 'task_assigned', 2, 'Bạn được giao việc trong Task mới: THIẾT KẾ GIAO DIỆN RELIOO', 'index.php?action=tasks', '2026-04-11 02:53:56'),
(25, 'task_assigned', 2, 'Bạn được giao việc trong Task mới: THIẾT KẾ GIAO DIỆN RELIOO', 'index.php?action=tasks', '2026-04-11 02:53:56'),
(26, 'task_assigned', 2, 'Bạn được giao việc trong Task mới: THIẾT KẾ GIAO DIỆN RELIOO', 'index.php?action=tasks', '2026-04-11 02:53:56'),
(27, 'task_approval', 3, 'Nhân viên Vũ Nhân Viên 1 đã gửi duyệt subtask: quýnh Đạt', 'index.php?action=tasks', '2026-04-11 02:55:20'),
(28, 'task_approved', 2, 'Subtask \'quýnh Đạt\' đã được DUYỆT và Hoàn thành!', 'index.php?action=tasks', '2026-04-11 02:55:41'),
(29, 'task_assigned', 2, 'Bạn được giao việc trong Task mới: Làm IT ngu', 'index.php?action=tasks', '2026-04-11 03:08:45'),
(30, 'task_assigned', 2, 'Bạn được giao việc trong Task mới: Làm IT ngu', 'index.php?action=tasks', '2026-04-11 03:08:45'),
(31, 'task_assigned', 2, 'Bạn được giao việc trong Task mới: IT ngu', 'index.php?action=tasks', '2026-04-11 03:10:35'),
(32, 'task_assigned', 2, 'Bạn được giao 1 việc mới trong Task: Làm IT ngu', 'index.php?action=tasks', '2026-04-11 03:14:43'),
(33, 'task_approval', 3, 'Nhân viên Vũ Nhân Viên 1 đã gửi duyệt subtask: quýnh Đạt', 'index.php?action=tasks', '2026-04-11 03:28:37'),
(34, 'task_approval', 3, 'Nhân viên Vũ Nhân Viên 1 đã gửi duyệt subtask: Ngu lắm con', 'index.php?action=tasks', '2026-04-11 03:28:53'),
(35, 'task_approval', 3, 'Nhân viên Vũ Nhân Viên 1 đã gửi duyệt subtask: Code web của ba Thành', 'index.php?action=tasks', '2026-04-11 03:35:24'),
(36, 'task_approval', 3, 'Nhân viên Vũ Nhân Viên 1 đã gửi duyệt subtask: Code web của ba Thành', 'index.php?action=tasks', '2026-04-11 03:35:27'),
(37, 'task_approval', 3, 'Nhân viên Vũ Nhân Viên 1 đã gửi duyệt subtask: Code web của ba Thành', 'index.php?action=tasks', '2026-04-11 03:35:27'),
(38, 'task_approval', 3, 'Nhân viên Vũ Nhân Viên 1 đã gửi duyệt subtask: Code web của ba Thành', 'index.php?action=tasks', '2026-04-11 03:35:28'),
(39, 'task_approval', 3, 'Nhân viên Vũ Nhân Viên 1 đã gửi duyệt subtask: Ngu lắm con', 'index.php?action=tasks', '2026-04-11 03:53:17'),
(40, 'task_approved', 2, 'Subtask \'quýnh Đạt\' đã được DUYỆT và Hoàn thành!', 'index.php?action=tasks', '2026-04-11 03:55:39'),
(41, 'task_approval', 3, 'Nhân viên Vũ Nhân Viên 1 đã gửi duyệt subtask: Học code web cùng ba Thành', 'index.php?action=tasks', '2026-04-11 08:23:06'),
(42, 'task_assigned', 2, 'Bạn được giao việc trong Task mới: ĂN SÁNG', 'index.php?action=tasks', '2026-04-11 08:31:35'),
(43, 'task_assigned', 2, 'Bạn được giao 1 việc mới trong Task: édfgjkl', 'index.php?action=tasks', '2026-04-11 08:34:34'),
(44, 'task_approval', 3, 'Nhân viên Vũ Nhân Viên 1 đã gửi duyệt subtask: quýnh Đạt', 'index.php?action=tasks&subtask_id=12', '2026-04-11 09:26:22'),
(45, 'task_approved', 2, 'Subtask \'quýnh Đạt\' đã được DUYỆT và Hoàn thành!', 'index.php?action=tasks&subtask_id=12', '2026-04-11 09:28:43'),
(46, 'task_extended', 2, 'Subtask \'fghjkdfghj\' đã được GIA HẠN đến 13/04/2026. Hãy thực hiện lại!', 'index.php?action=tasks&subtask_id=23', '2026-04-11 09:30:30'),
(47, 'task_extended', 2, 'Subtask \'Code web của ba Thành\' đã được GIA HẠN đến 15/04/2026. Hãy thực hiện lại!', 'index.php?action=tasks&subtask_id=18', '2026-04-11 09:31:15'),
(48, 'task_approval', 3, 'Nhân viên Vũ Nhân Viên 1 đã gửi duyệt subtask: Code web của ba Thành', 'index.php?action=tasks&subtask_id=18', '2026-04-11 09:32:40'),
(49, 'task_approved', 2, 'Subtask \'Code web của ba Thành\' đã được DUYỆT và Hoàn thành!', 'index.php?action=tasks&subtask_id=18', '2026-04-11 09:33:00'),
(50, 'task_assigned', 2, 'Bạn được giao việc trong Task mới: sdfsdfsdf', 'index.php?action=tasks', '2026-04-11 09:34:38'),
(51, 'task_rejected', 2, 'Subtask \'Học code web cùng ba Thành\' bị TỪ CHỐI: ừ gà', 'index.php?action=tasks&subtask_id=20', '2026-04-11 09:39:04'),
(52, 'task_assigned', 2, 'Bạn được giao việc trong Task mới: Scroll Test Task', 'index.php?action=tasks', '2026-04-11 09:53:12'),
(53, 'task_assigned', 2, 'Bạn được giao 1 việc mới trong Task: sdfsdfsdf', 'index.php?action=tasks', '2026-04-11 09:57:14'),
(54, 'task_assigned', 2, 'Bạn được giao 1 việc mới trong Task: sdfsdfsdf', 'index.php?action=tasks', '2026-04-11 09:57:16'),
(55, 'task_assigned', 2, 'Bạn được giao 1 việc mới trong Task: sdfsdfsdf', 'index.php?action=tasks', '2026-04-11 09:57:17'),
(56, 'task_assigned', 2, 'Bạn được giao 1 việc mới trong Task: sdfsdfsdf', 'index.php?action=tasks', '2026-04-11 09:57:17'),
(57, 'task_approved', 1, 'Subtask \'ừ\' đã được DUYỆT và Hoàn thành!', 'index.php?action=tasks&subtask_id=5', '2026-04-11 13:44:42'),
(58, 'task_assigned', 1, 'Bạn được giao việc trong Task mới: cfgvhbjnkml', 'index.php?action=tasks', '2026-04-11 14:03:51'),
(59, 'task_extended', 1, 'Subtask \'fghjk\' đã được GIA HẠN đến 23/04/2026. Hãy thực hiện lại!', 'index.php?action=tasks&subtask_id=22', '2026-04-11 14:05:12'),
(60, 'task_assigned', 2, 'Bạn được giao 1 việc mới trong Task: Scroll Test Task', 'index.php?action=tasks', '2026-04-11 14:07:17'),
(61, 'task_approval', 3, 'Nhân viên Vũ Nhân Viên 1 đã gửi duyệt subtask: sub 2', 'index.php?action=tasks&subtask_id=31', '2026-04-11 14:07:47'),
(62, 'task_approved', 2, 'Subtask \'sub 2\' đã được DUYỆT! Vui lòng kéo subtask sang cột Hoàn thành và viết báo cáo AI.', 'index.php?action=tasks', '2026-04-11 14:08:10'),
(63, 'task_rejected', 2, 'Subtask \'sub 2\' bị TỪ CHỐI: ngu', 'index.php?action=tasks&subtask_id=31', '2026-04-11 14:08:40'),
(64, 'task_approval', 3, 'Nhân viên Vũ Nhân Viên 1 đã gửi duyệt subtask: sub 2', 'index.php?action=tasks&subtask_id=31', '2026-04-11 14:13:12'),
(65, 'task_approved', 2, 'Subtask \'sub 2\' đã được DUYỆT và Hoàn thành!', 'index.php?action=tasks&subtask_id=31', '2026-04-11 14:16:57'),
(66, 'task_approval', 3, 'Nhân viên Vũ Nhân Viên 1 đã gửi duyệt subtask: Sub 1', 'index.php?action=tasks&subtask_id=25', '2026-04-11 14:27:12'),
(67, 'task_approved', 2, 'Subtask \'Sub 1\' đã được DUYỆT! Vui lòng kéo subtask sang cột Hoàn thành và viết báo cáo AI.', 'index.php?action=tasks', '2026-04-11 14:27:24'),
(68, 'task_assigned', 2, 'Bạn được giao việc trong Task mới: Khai phá công nghệ, nâng tầm kinh tế', 'index.php?action=tasks', '2026-04-11 15:19:21'),
(69, 'task_approval', 3, 'Nhân viên Vũ Nhân Viên 1 đã gửi duyệt subtask: Khảo sát thị trường', 'index.php?action=tasks&subtask_id=32', '2026-04-11 15:20:26'),
(70, 'task_approval', 3, 'Nhân viên Vũ Nhân Viên 1 đã gửi duyệt subtask: Khảo sát người dùng', 'index.php?action=tasks&subtask_id=33', '2026-04-11 15:20:39'),
(71, 'task_approval', 3, 'Nhân viên Vũ Nhân Viên 1 đã gửi duyệt subtask: Tổng kết kết quả nghiên cứu', 'index.php?action=tasks&subtask_id=34', '2026-04-11 15:20:47'),
(72, 'task_approved', 2, 'Subtask \'Khảo sát thị trường\' đã được DUYỆT! Vui lòng kéo subtask sang cột Hoàn thành và viết báo cáo AI.', 'index.php?action=tasks', '2026-04-11 15:21:00'),
(73, 'task_approved', 2, 'Subtask \'Khảo sát người dùng\' đã được DUYỆT! Vui lòng kéo subtask sang cột Hoàn thành và viết báo cáo AI.', 'index.php?action=tasks', '2026-04-11 15:21:06'),
(74, 'task_approved', 2, 'Subtask \'Tổng kết kết quả nghiên cứu\' đã được DUYỆT! Vui lòng kéo subtask sang cột Hoàn thành và viết báo cáo AI.', 'index.php?action=tasks', '2026-04-11 15:21:12'),
(75, 'task_approved', 2, 'Subtask \'Sub 1\' đã được DUYỆT! Vui lòng kéo subtask sang cột Hoàn thành và viết báo cáo AI.', 'index.php?action=tasks', '2026-04-11 15:59:45'),
(76, 'task_approval', 3, 'Nhân viên Vũ Nhân Viên 1 đã gửi duyệt subtask: Học code web cùng ba Thành', 'index.php?action=tasks&subtask_id=20', '2026-04-12 21:26:25'),
(77, 'task_extended', 2, 'Subtask \'Học code web cùng ba Thành\' đã được GIA HẠN đến 15/04/2026. Hãy thực hiện lại!', 'index.php?action=tasks&subtask_id=20', '2026-04-12 21:27:59'),
(78, 'task_approval', 3, 'Nhân viên Vũ Nhân Viên 1 đã gửi duyệt subtask: đói vndajkv', 'index.php?action=tasks&subtask_id=27', '2026-04-12 21:36:16'),
(79, 'task_approved', 1, 'Subtask \'ùa\' đã được DUYỆT! Vui lòng kéo subtask sang cột Hoàn thành và viết báo cáo AI.', 'index.php?action=tasks', '2026-04-12 22:13:05'),
(80, 'task_approval', 1, 'Nhân viên Nguyễn Văn CEO đã gửi duyệt subtask: Thuyết trình cùng ba Hói', 'index.php?action=tasks&subtask_id=19', '2026-04-12 22:20:41'),
(81, 'task_extended', 1, 'Subtask \'Thuyết trình cùng ba Hói\' đã được GIA HẠN đến 15/04/2026. Hãy thực hiện lại!', 'index.php?action=tasks&subtask_id=19', '2026-04-12 22:23:36'),
(82, 'task_approval', 1, 'Nhân viên Nguyễn Văn CEO đã gửi duyệt subtask: Thuyết trình cùng ba Hói', 'index.php?action=tasks&subtask_id=19', '2026-04-12 22:24:01'),
(83, 'task_approved', 1, 'Subtask \'Thuyết trình cùng ba Hói\' đã được DUYỆT! Vui lòng kéo subtask sang cột Hoàn thành và viết báo cáo AI.', 'index.php?action=tasks', '2026-04-12 22:24:14'),
(84, 'SOCIAL_LIKE', 2, 'Trần IT Trưởng đã thích bài viết của bạn.', 'index.php?action=social&post_id=11', '2026-04-12 23:09:09'),
(86, 'SOCIAL_COMMENT', 3, 'Vũ Nhân Viên 1 đã bình luận về bài viết của bạn.', 'index.php?action=social&post_id=20', '2026-04-12 23:22:46'),
(87, 'SOCIAL_LIKE', 2, 'Trần IT Trưởng đã thích bài viết của bạn.', 'index.php?action=social&post_id=11', '2026-04-12 23:37:10'),
(88, 'SOCIAL_LIKE', 2, 'Trần IT Trưởng đã thích bài viết của bạn.', 'index.php?action=social&post_id=11', '2026-04-12 23:37:12'),
(89, 'SOCIAL_LIKE', 2, 'Trần IT Trưởng đã thích bài viết của bạn.', 'index.php?action=social&post_id=11', '2026-04-12 23:37:17'),
(90, 'SOCIAL_LIKE', 2, 'Trần IT Trưởng đã thích bài viết của bạn.', 'index.php?action=social&post_id=11', '2026-04-12 23:37:19'),
(91, 'SOCIAL_COMMENT', 2, 'Trần IT Trưởng đã bình luận về bài viết của bạn.', 'index.php?action=social&post_id=11', '2026-04-12 23:37:36'),
(94, 'SOCIAL_COMMENT', 3, 'Vũ Nhân Viên 1 đã bình luận về bài viết của bạn.', 'index.php?action=social&post_id=19#comment-45', '2026-04-12 23:49:36'),
(96, 'SOCIAL_COMMENT', 3, 'Vũ Nhân Viên 1 đã bình luận về bài viết của bạn.', 'index.php?action=social&post_id=19#comment-46', '2026-04-12 23:50:09'),
(97, 'SOCIAL_LIKE', 2, 'Trần IT Trưởng đã thích bài viết của bạn.', 'index.php?action=social&post_id=4', '2026-04-12 23:57:36'),
(100, 'SOCIAL_COMMENT', 2, 'Trần IT Trưởng đã bình luận về bài viết của bạn.', 'index.php?action=social&post_id=3#comment-48', '2026-04-12 23:58:13'),
(101, 'SOCIAL_LIKE', 2, 'Trần IT Trưởng đã thích bài viết của bạn.', 'index.php?action=social&post_id=3', '2026-04-12 23:58:22'),
(110, 'SOCIAL_COMMENT', 3, 'Vũ Nhân Viên 1 đã bình luận về bài viết của bạn.', 'index.php?action=social&post_id=19#comment-50', '2026-04-13 00:07:50'),
(111, 'SOCIAL_COMMENT', 2, 'Trần IT Trưởng đã bình luận về bài viết của bạn.', 'index.php?action=social&post_id=11#comment-53', '2026-04-13 00:44:47'),
(112, 'SOCIAL_COMMENT', 2, 'Trần IT Trưởng đã bình luận về bài viết của bạn.', 'index.php?action=social&post_id=11#comment-54', '2026-04-13 00:44:57'),
(114, 'SOCIAL_COMMENT', 3, 'Vũ Nhân Viên 1 đã bình luận về bài viết của bạn.', 'index.php?action=social&post_id=20#comment-56', '2026-04-13 00:55:13'),
(121, 'SOCIAL_LIKE', 3, 'Vũ Nhân Viên 1 đã thích bài viết của bạn.', 'index.php?action=social&post_id=2', '2026-04-14 15:35:10'),
(123, 'SOCIAL_LIKE', 1, 'Nguyễn Văn CEO đã thích bài viết của bạn.', 'index.php?action=social&post_id=4', '2026-04-14 16:00:08'),
(124, 'SOCIAL_LIKE', 1, 'Nguyễn Văn CEO đã thích bài viết của bạn.', 'index.php?action=social&post_id=3', '2026-04-14 16:00:09'),
(152, 'task_approval', 3, 'Nhân viên Vũ Nhân Viên 1 đã gửi duyệt subtask: đói vndajkv', 'index.php?action=tasks&subtask_id=26', '2026-04-14 23:04:44'),
(153, 'task_approval', 3, 'Nhân viên Vũ Nhân Viên 1 đã gửi duyệt subtask: đói vndajkv', 'index.php?action=tasks&subtask_id=26', '2026-04-14 23:04:48'),
(161, 'SOCIAL_LIKE', 3, 'Vũ Nhân Viên 1 đã thích bài viết của bạn.', 'index.php?action=social&post_id=20', '2026-04-15 07:55:03'),
(175, 'task_assigned', 1, 'Bạn được giao việc trong Task mới: dâdadwdad', 'index.php?action=tasks', '2026-04-15 08:55:42'),
(177, 'SOCIAL_LIKE', 1, 'Nguyễn Văn CEO đã thích bài viết của bạn.', 'index.php?action=social&post_id=20', '2026-04-15 08:56:39'),
(178, 'SOCIAL_LIKE', 1, 'Nguyễn Văn CEO đã thích bài viết của bạn.', 'index.php?action=social&post_id=11', '2026-04-15 08:56:45'),
(185, 'SOCIAL_LIKE', 3, 'Vũ Nhân Viên 1 đã thích bài viết của bạn.', 'index.php?action=social&post_id=8', '2026-04-15 08:57:09'),
(186, 'task_assigned', 1, 'Bạn được giao việc trong Task mới: cxzzxc', 'index.php?action=tasks', '2026-04-15 09:03:36'),
(187, 'task_assigned', 1, 'Bạn được giao việc trong Task mới: cxzzxc', 'index.php?action=tasks', '2026-04-15 09:04:40'),
(188, 'task_assigned', 1, 'Bạn được giao việc trong Task mới: cxzzxc', 'index.php?action=tasks', '2026-04-15 09:12:37'),
(189, 'task_assigned', 1, 'Bạn được giao việc trong Task mới: szvs', 'index.php?action=tasks', '2026-04-15 09:15:43'),
(190, 'task_approval', 3, 'Nhân viên Vũ Nhân Viên 1 đã gửi duyệt subtask: áaf', 'index.php?action=tasks&subtask_id=35', '2026-04-15 09:57:31'),
(191, 'task_approved', 1, 'Subtask \'áaf\' đã được DUYỆT! Vui lòng kéo subtask sang cột Hoàn thành và viết báo cáo AI.', 'index.php?action=tasks', '2026-04-15 09:58:16');

-- --------------------------------------------------------

--
-- Table structure for table `notification_user`
--

CREATE TABLE `notification_user` (
  `notification_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `is_read` tinyint(1) DEFAULT 0,
  `read_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `notification_user`
--

INSERT INTO `notification_user` (`notification_id`, `user_id`, `is_read`, `read_at`) VALUES
(1, 1, 1, '2026-04-11 13:48:19'),
(2, 3, 1, '2026-04-09 11:59:42'),
(3, 3, 1, '2026-04-09 12:18:13'),
(4, 1, 1, '2026-04-11 15:51:19'),
(5, 3, 1, '2026-04-09 12:18:13'),
(6, 1, 1, '2026-04-11 15:51:19'),
(7, 3, 1, '2026-04-09 12:18:13'),
(8, 1, 1, '2026-04-11 15:51:19'),
(9, 3, 1, '2026-04-09 12:18:13'),
(10, 1, 1, '2026-04-11 15:51:19'),
(11, 3, 1, '2026-04-09 12:18:13'),
(12, 1, 1, '2026-04-11 15:51:19'),
(13, 1, 1, '2026-04-11 15:51:19'),
(14, 3, 1, '2026-04-11 02:54:24'),
(15, 3, 1, '2026-04-11 02:54:24'),
(16, 1, 1, '2026-04-11 15:51:19'),
(17, 3, 1, '2026-04-11 02:54:24'),
(18, 3, 1, '2026-04-11 02:54:24'),
(19, 1, 1, '2026-04-11 15:51:19'),
(21, 3, 1, '2026-04-11 02:54:24'),
(22, 1, 1, '2026-04-11 15:51:19'),
(24, 3, 1, '2026-04-11 02:54:24'),
(25, 1, 1, '2026-04-11 15:51:19'),
(27, 2, 1, '2026-04-11 03:04:23'),
(28, 3, 1, '2026-04-11 03:00:25'),
(29, 3, 1, '2026-04-11 03:26:02'),
(30, 1, 1, '2026-04-11 12:03:02'),
(31, 3, 1, '2026-04-11 03:26:02'),
(32, 3, 1, '2026-04-11 03:26:02'),
(33, 2, 1, '2026-04-11 09:38:23'),
(34, 1, 1, '2026-04-11 12:02:34'),
(35, 2, 1, '2026-04-11 09:38:23'),
(36, 2, 1, '2026-04-11 09:38:23'),
(37, 2, 1, '2026-04-11 09:38:23'),
(38, 2, 1, '2026-04-11 09:38:23'),
(39, 1, 1, '2026-04-11 13:44:30'),
(40, 3, 1, '2026-04-11 09:25:39'),
(41, 2, 1, '2026-04-11 09:38:23'),
(42, 3, 1, '2026-04-11 09:25:39'),
(43, 3, 1, '2026-04-11 09:25:34'),
(44, 2, 1, '2026-04-11 09:38:23'),
(45, 3, 1, '2026-04-11 09:28:52'),
(46, 3, 1, '2026-04-11 09:31:28'),
(47, 3, 1, '2026-04-11 09:39:45'),
(48, 2, 1, '2026-04-11 09:38:23'),
(49, 3, 1, '2026-04-11 09:39:45'),
(50, 3, 1, '2026-04-11 09:39:45'),
(51, 3, 1, '2026-04-11 09:39:45'),
(52, 3, 1, '2026-04-11 11:20:03'),
(53, 3, 1, '2026-04-11 11:20:03'),
(54, 3, 1, '2026-04-11 11:20:03'),
(55, 3, 1, '2026-04-11 11:20:03'),
(56, 3, 1, '2026-04-11 11:20:03'),
(57, 3, 1, '2026-04-11 13:56:28'),
(58, 3, 1, '2026-04-12 23:48:07'),
(59, 3, 1, '2026-04-12 23:48:07'),
(60, 3, 1, '2026-04-11 14:07:28'),
(61, 2, 1, '2026-04-11 14:08:23'),
(62, 3, 1, '2026-04-12 23:48:07'),
(63, 3, 1, '2026-04-12 23:48:07'),
(64, 2, 1, '2026-04-11 14:16:44'),
(65, 3, 1, '2026-04-11 15:14:24'),
(66, 2, 0, NULL),
(67, 3, 1, '2026-04-11 15:14:22'),
(68, 3, 1, '2026-04-12 23:48:07'),
(69, 2, 0, NULL),
(70, 2, 0, NULL),
(71, 2, 0, NULL),
(72, 3, 1, '2026-04-12 23:48:07'),
(73, 3, 1, '2026-04-12 23:48:07'),
(74, 3, 1, '2026-04-12 23:48:07'),
(75, 3, 1, '2026-04-12 23:48:07'),
(76, 2, 0, NULL),
(77, 3, 1, '2026-04-12 23:48:07'),
(78, 2, 0, NULL),
(79, 3, 1, '2026-04-12 23:48:07'),
(80, 2, 1, '2026-04-12 23:35:37'),
(82, 2, 1, '2026-04-12 23:28:10'),
(84, 3, 1, '2026-04-12 23:48:07'),
(86, 2, 1, '2026-04-12 23:35:12'),
(87, 3, 1, '2026-04-12 23:48:07'),
(88, 3, 1, '2026-04-12 23:48:07'),
(89, 3, 1, '2026-04-12 23:48:07'),
(90, 3, 1, '2026-04-12 23:47:55'),
(91, 3, 1, '2026-04-12 23:44:26'),
(94, 2, 1, '2026-04-12 23:50:57'),
(96, 2, 1, '2026-04-12 23:51:06'),
(97, 3, 1, '2026-04-12 23:59:21'),
(100, 3, 1, '2026-04-12 23:59:03'),
(101, 3, 1, '2026-04-12 23:59:10'),
(110, 2, 1, '2026-04-13 00:08:54'),
(111, 3, 1, '2026-04-13 00:45:22'),
(112, 3, 1, '2026-04-13 00:47:47'),
(114, 2, 1, '2026-04-13 00:55:59'),
(121, 1, 1, '2026-04-15 08:49:35'),
(123, 3, 0, NULL),
(124, 3, 1, '2026-04-15 08:47:49'),
(152, 2, 0, NULL),
(153, 2, 0, NULL),
(161, 2, 1, '2026-04-15 09:22:59'),
(175, 3, 0, NULL),
(177, 2, 1, '2026-04-15 09:22:56'),
(178, 3, 0, NULL),
(185, 1, 1, '2026-04-15 08:57:16'),
(186, 3, 0, NULL),
(187, 3, 0, NULL),
(188, 3, 0, NULL),
(189, 2, 0, NULL),
(190, 1, 1, '2026-04-15 09:58:10'),
(191, 3, 1, '2026-04-15 09:58:29');

-- --------------------------------------------------------

--
-- Table structure for table `posts`
--

CREATE TABLE `posts` (
  `id` int(11) NOT NULL,
  `author_id` int(11) NOT NULL,
  `department_id` int(11) DEFAULT NULL,
  `task_report_id` int(11) DEFAULT NULL,
  `visibility` enum('Public','Department','Private','Announcement') DEFAULT 'Public',
  `content_html` text NOT NULL,
  `is_ai_generated` tinyint(1) DEFAULT 0,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `posts`
--

INSERT INTO `posts` (`id`, `author_id`, `department_id`, `task_report_id`, `visibility`, `content_html`, `is_ai_generated`, `created_at`, `updated_at`) VALUES
(1, 3, NULL, NULL, 'Public', 'hreheh', 0, '2026-04-09 09:50:46', '2026-04-09 09:50:46'),
(2, 1, NULL, NULL, 'Public', 'uhjcdgfdfdghfgfhgfdfd', 0, '2026-04-11 00:22:57', '2026-04-11 00:22:57'),
(3, 3, NULL, NULL, 'Public', 'Chúc mừng công ty đã lên sàn chứng khoán', 0, '2026-04-11 03:00:00', '2026-04-11 03:00:00'),
(4, 3, NULL, NULL, 'Public', 'Chúc mừng công ty đã lên sàn chứng khoán', 0, '2026-04-11 03:00:01', '2026-04-11 03:00:01'),
(5, 1, NULL, NULL, 'Public', 'hòn hun', 0, '2026-04-11 03:11:05', '2026-04-11 03:11:05'),
(6, 1, NULL, NULL, '', 'lô', 0, '2026-04-11 12:46:48', '2026-04-11 12:46:48'),
(7, 1, NULL, NULL, '', 'hé lô', 0, '2026-04-11 12:47:04', '2026-04-11 12:47:04'),
(8, 1, NULL, NULL, 'Public', 'hé lô\r\n', 0, '2026-04-11 12:47:38', '2026-04-11 12:47:38'),
(9, 2, 3, NULL, 'Department', 'hé lô\r\n', 0, '2026-04-11 12:48:59', '2026-04-11 12:48:59'),
(10, 1, NULL, NULL, 'Announcement', 'hêllo', 0, '2026-04-11 12:53:51', '2026-04-11 12:53:51'),
(11, 3, NULL, NULL, 'Public', 'Tôi nè Phát', 0, '2026-04-11 12:59:11', '2026-04-11 12:59:11'),
(13, 3, 3, 1, 'Department', '<div class=\'ai-post\'><h6 class=\'fw-bold text-primary mb-2\'>🚀 Báo cáo tiến độ: Khảo sát thị trường</h6>&quot;Báo Cáo Tiến Độ: Khảo Sát Thị Trường<br />\n<br />\nMới đây, chúng tôi đã tiến hành khảo sát thị trường công nghệ hiện nay. Trong khuôn khổ dự án, chúng tôi đã thực hiện điều tra thị trường trà sữa.<br />\n<br />\nQua quá trình khảo sát, chúng tôi đã thu thập được những thông tin quý giá về thị trường trà sữa. Tuy nhiên, quá trình thực hiện cũng cho chúng tôi một số kinh nghiệm rút ra về cách quản lý và phối hợp trong nhóm.<br />\n<br />\nChúng tôi sẽ tiếp tục cải thiện và hoàn thiện quy trình khảo sát để mang lại kết quả tốt hơn trong tương lai. Cảm ơn sự hỗ trợ và hợp tác của tất cả các thành viên trong nhóm.&quot;</div>', 1, '2026-04-11 15:23:00', '2026-04-11 15:23:00'),
(14, 3, 3, 2, 'Department', '<div class=\'ai-post\'><h6 class=\'fw-bold text-primary mb-2\'>🚀 Báo cáo tiến độ: Khảo sát người dùng</h6>&quot;Báo cáo tiến độ: Khảo sát người dùng<br />\n<br />\nChúng tôi đã hoàn thành khảo sát thị trường công nghệ ET. Quá trình thực hiện đã giúp chúng tôi tích lũy kinh nghiệm và ghi nhận những thông tin quan trọng. Chúng tôi sẽ tiếp tục cải thiện và hoàn thiện quy trình cho những dự án tương lai.<br />\n<br />\n#KhảoSátThịTrường #CôngNghệET #TiếnĐộDựÁn&quot;</div>', 1, '2026-04-11 15:25:11', '2026-04-11 15:25:11'),
(15, 3, 3, 3, 'Department', '<div class=\'ai-post\'><h6 class=\'fw-bold text-primary mb-2\'>🚀 Báo cáo tiến độ: Tổng kết kết quả nghiên cứu</h6>&quot;Tổng kết kết quả nghiên cứu: Chúng tôi đã áp dụng công nghệ để thực hiện nghiên cứu và đạt được những kết quả đáng kể. Qua quá trình này, chúng tôi đã tích lũy được kinh nghiệm quý giá về việc thu thập và phân tích dữ liệu. Để cải thiện trong tương lai, chúng tôi sẽ tập trung vào việc nâng cao kỹ năng khai thác thông tin trên các nền tảng như web. Cảm ơn sự hỗ trợ và hợp tác!&quot; #Tổng_kết_nghiên_cứu #Kết_quả_thành_công</div>', 1, '2026-04-11 15:26:33', '2026-04-11 15:26:33'),
(18, 3, 3, NULL, 'Department', 'xdfcgvhbjnkml;', 0, '2026-04-11 15:35:50', '2026-04-11 15:35:50'),
(19, 2, 3, 5, 'Department', '<div class=\'ai-post\'><h5 class=\'fw-bold text-success mb-2\'>🏆 Tổng kết dự án: Khai phá công nghệ, nâng tầm kinh tế</h5>Dự án &quot;Khai phá công nghệ, nâng tầm kinh tế&quot; của chúng ta đã đạt được những kết quả đáng kể và đầy ấn tượng! Tôi muốn dành một chút thời gian để tổng kết và biểu dương toàn bộ đội ngũ đã làm việc không ngừng nghỉ để mang lại thành công cho dự án này.<br />\n<br />\nTrước hết, chúng ta đã tiến hành một cuộc khảo sát thị trường công nghệ ET và trà sữa, thu thập được những thông tin quý giá về thị trường và tích lũy kinh nghiệm về cách quản lý và phối hợp trong nhóm. Quá trình này không chỉ giúp chúng ta hiểu rõ hơn về thị trường mà còn giúp chúng ta cải thiện và hoàn thiện quy trình khảo sát cho những dự án tương lai.<br />\n<br />\nTiếp theo, chúng ta đã áp dụng công nghệ để thực hiện nghiên cứu và đạt được những kết quả đáng kể. Qua quá trình này, chúng ta đã tích lũy được kinh nghiệm quý giá về việc thu thập và phân tích dữ liệu, và đã xác định được hướng cải thiện trong tương lai, đó là nâng cao kỹ năng khai thác thông tin trên các nền tảng như web.<br />\n<br />\nTôi muốn biểu dương toàn bộ đội ngũ vì sự nỗ lực và hợp tác không ngừng nghỉ. Mỗi thành viên trong nhóm đã đóng góp một phần quan trọng vào thành công của dự án, và tôi rất tự hào về kết quả mà chúng ta đã đạt được.<br />\n<br />\nDự án &quot;Khai phá công nghệ, nâng tầm kinh tế&quot; không chỉ là một dự án thành công, mà còn là một bước tiến quan trọng trong việc nâng cao kỹ năng và kiến thức của chúng ta. Chúng ta đã chứng minh rằng với sự hợp tác và nỗ lực, chúng ta có thể đạt được những kết quả đáng kể và đóng góp vào sự phát triển của công ty.<br />\n<br />\nCảm ơn tất cả các thành viên trong nhóm vì sự đóng góp và hợp tác. Hãy tiếp tục làm việc cùng nhau để đạt được những thành công mới và nâng cao vị thế của công ty trong lĩnh vực công nghệ! #KhaiPháCôngNghệ #NângTầmKinhTế #ThànhCông #ĐộiNgũ</div>', 1, '2026-04-11 16:00:24', '2026-04-11 16:00:24'),
(20, 2, NULL, NULL, 'Public', 'Mọi ng ai có nhu cầu mua cháy liên hệ SDT\r\n5462XXx65523 (IT Trần)', 0, '2026-04-12 16:04:27', '2026-04-12 16:04:27'),
(21, 1, 1, 6, 'Department', '<div class=\'ai-post\'><h6 class=\'fw-bold text-primary mb-2\'>🚀 Báo cáo tiến độ: Thuyết trình cùng ba Hói</h6>&quot;Báo cáo tiến độ: Thuyết trình cùng ba Hói<br />\n<br />\nChúng tôi đã hoàn thành việc thảo luận và thuyết trình cùng ba Hói. Quá trình thực hiện đã được hỗ trợ bởi công nghệ hiện đại, giúp tăng cường hiệu quả và chất lượng.<br />\n<br />\nMột số kinh nghiệm rút ra từ quá trình này là tầm quan trọng của việc áp dụng công nghệ vào công việc, giúp mở rộng phạm vi và khả năng tiếp cận toàn cầu.<br />\n<br />\nĐể cải thiện trong tương lai, chúng tôi sẽ tiếp tục áp dụng công nghệ và tinh chỉnh quy trình để đạt được kết quả tốt hơn. Cảm ơn sự hỗ trợ và hợp tác!&quot; #ThuyetTrinh #BaHoi #CongTy #TienDo</div>', 1, '2026-04-12 22:24:44', '2026-04-12 22:24:44'),
(22, 3, 3, NULL, 'Department', 'dm bộ phận kỹ thuật đâu, web lag quá', 0, '2026-04-14 16:08:49', '2026-04-14 16:08:49');

-- --------------------------------------------------------

--
-- Table structure for table `post_edit_history`
--

CREATE TABLE `post_edit_history` (
  `id` int(11) NOT NULL,
  `post_id` int(11) NOT NULL,
  `old_content` text NOT NULL,
  `changed_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `post_media`
--

CREATE TABLE `post_media` (
  `id` int(11) NOT NULL,
  `post_id` int(11) NOT NULL,
  `media_type` enum('Image','Video') DEFAULT 'Image',
  `media_url` varchar(500) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `post_media`
--

INSERT INTO `post_media` (`id`, `post_id`, `media_type`, `media_url`) VALUES
(1, 2, 'Image', 'https://i.ibb.co/j9K0Y2b1/php-CB72.png'),
(2, 5, 'Image', 'https://i.ibb.co/tMHPq8n6/php-BB68.jpg'),
(3, 11, 'Image', 'https://i.ibb.co/RkpXtQVM/php4415.jpg'),
(5, 20, 'Image', 'https://i.ibb.co/wZrjj8yz/php48DB.webp');

-- --------------------------------------------------------

--
-- Table structure for table `post_reactions`
--

CREATE TABLE `post_reactions` (
  `id` int(11) NOT NULL,
  `post_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `type` enum('Like','Heart','Haha','Wow','Sad','Angry') DEFAULT 'Like',
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `post_reactions`
--

INSERT INTO `post_reactions` (`id`, `post_id`, `user_id`, `type`, `created_at`) VALUES
(5, 1, 1, 'Heart', '2026-04-09 10:08:23'),
(6, 2, 1, 'Heart', '2026-04-11 00:23:13'),
(61, 3, 3, 'Heart', '2026-04-11 11:38:21'),
(62, 9, 3, 'Heart', '2026-04-11 12:49:16'),
(64, 10, 2, 'Heart', '2026-04-11 15:27:46'),
(65, 5, 3, 'Heart', '2026-04-12 15:17:35'),
(70, 11, 2, 'Heart', '2026-04-12 23:37:19'),
(74, 4, 2, 'Heart', '2026-04-12 23:57:36'),
(76, 3, 2, 'Heart', '2026-04-12 23:58:22'),
(83, 19, 3, 'Heart', '2026-04-13 00:07:42'),
(84, 19, 2, 'Heart', '2026-04-13 00:42:19'),
(85, 11, 3, 'Heart', '2026-04-13 00:45:31'),
(94, 1, 3, 'Heart', '2026-04-14 15:27:32'),
(99, 5, 1, 'Heart', '2026-04-14 16:00:07'),
(100, 4, 1, 'Heart', '2026-04-14 16:00:08'),
(101, 3, 1, 'Heart', '2026-04-14 16:00:09'),
(113, 20, 2, 'Heart', '2026-04-14 16:09:20'),
(138, 20, 3, 'Heart', '2026-04-15 07:55:03'),
(153, 20, 1, 'Heart', '2026-04-15 08:56:39'),
(154, 11, 1, 'Heart', '2026-04-15 08:56:45'),
(161, 8, 3, 'Heart', '2026-04-15 08:57:09'),
(162, 4, 3, 'Heart', '2026-04-15 10:10:23');

-- --------------------------------------------------------

--
-- Table structure for table `roles`
--

CREATE TABLE `roles` (
  `id` int(11) NOT NULL,
  `role_name` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `roles`
--

INSERT INTO `roles` (`id`, `role_name`) VALUES
(1, 'CEO'),
(2, 'Leader'),
(3, 'Staff'),
(4, 'Admin');

-- --------------------------------------------------------

--
-- Table structure for table `subtasks`
--

CREATE TABLE `subtasks` (
  `id` int(11) NOT NULL,
  `task_id` int(11) NOT NULL,
  `assignee_id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `deadline` datetime DEFAULT NULL,
  `priority` enum('Low','Medium','High') DEFAULT 'Medium',
  `status` enum('To Do','In Progress','Pending','Done') DEFAULT 'To Do',
  `completion_rating` float DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `feedback` text DEFAULT NULL,
  `is_extended` tinyint(1) DEFAULT 0,
  `is_rejected` tinyint(1) DEFAULT 0,
  `is_approved` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `subtasks`
--

INSERT INTO `subtasks` (`id`, `task_id`, `assignee_id`, `title`, `description`, `deadline`, `priority`, `status`, `completion_rating`, `created_at`, `updated_at`, `feedback`, `is_extended`, `is_rejected`, `is_approved`) VALUES
(1, 1, 3, 'Thiết kế Giao diện Kanban', 'Làm giao diện giống GitHub', '2026-04-30 00:00:00', 'Medium', 'Done', NULL, '2026-04-08 22:54:32', '2026-04-09 12:08:11', 'gà', 0, 0, 0),
(2, 1, 3, 'Xây dựng API Phê duyệt', 'Viết logic duyệt task', '2026-05-05 00:00:00', 'Medium', 'Done', NULL, '2026-04-08 22:54:32', '2026-04-09 12:17:24', 'làm lại đi', 0, 0, 0),
(3, 2, 3, 'âcsc', '', NULL, 'Medium', 'Done', NULL, '2026-04-09 09:49:45', '2026-04-11 02:34:56', NULL, 0, 0, 0),
(4, 1, 3, 'làm lẹ mày', '', NULL, 'Medium', 'Done', NULL, '2026-04-09 12:17:34', '2026-04-09 12:18:08', NULL, 0, 0, 0),
(5, 3, 3, 'ừ', '', NULL, 'Medium', 'Done', NULL, '2026-04-11 01:02:16', '2026-04-11 13:44:42', NULL, 0, 0, 0),
(6, 2, 3, 'ùa', '', NULL, 'Medium', 'Pending', NULL, '2026-04-11 01:05:40', '2026-04-12 22:13:05', NULL, 0, 0, 1),
(7, 2, 3, 'Ngu lắm con', 'dạ chú', '2026-04-16 00:00:00', 'High', 'Pending', NULL, '2026-04-11 02:33:54', '2026-04-11 03:28:53', NULL, 0, 0, 0),
(8, 2, 3, 'Ngu lắm con', 'dạ chú', '2026-04-16 00:00:00', 'High', 'Pending', NULL, '2026-04-11 02:33:56', '2026-04-11 03:53:16', NULL, 0, 0, 0),
(9, 4, 3, 'quýnh Đạt', 'quýnh tới khi nó đi kh nổi', '2026-11-04 00:00:00', 'High', 'Done', NULL, '2026-04-11 02:53:55', '2026-04-11 02:55:41', NULL, 0, 0, 0),
(10, 4, 1, 'chặt tay th Phát', 'chặt 1 tay để nó kh nhắn tin được nma đủ để code web', '2026-11-04 00:00:00', 'High', 'To Do', NULL, '2026-04-11 02:53:55', '2026-04-11 02:53:55', NULL, 0, 0, 0),
(11, 4, 2, 'chăm sóc đặc biệt cho Quân', 'mua thuốc cho bạn, để bạn hồi sức code web', '2026-11-04 00:00:00', 'High', 'To Do', NULL, '2026-04-11 02:53:55', '2026-04-11 02:53:55', NULL, 0, 0, 0),
(18, 7, 3, 'Code web của ba Thành', 'Dùng đầy đủ quan hệ OOP ko là bị trừ điểm', '2026-04-15 00:00:00', 'High', 'Done', NULL, '2026-04-11 03:08:45', '2026-04-11 09:33:00', NULL, 1, 0, 0),
(19, 7, 1, 'Thuyết trình cùng ba Hói', 'Cùng thảo luận nha', '2026-04-15 00:00:00', 'High', 'Done', NULL, '2026-04-11 03:08:45', '2026-04-12 22:24:44', NULL, 1, 0, 1),
(20, 8, 3, 'Học code web cùng ba Thành', 'Phải dùng đủ quan hệ OOP không là bị trừ điểm', '2026-04-15 00:00:00', 'High', 'In Progress', NULL, '2026-04-11 03:10:34', '2026-04-12 21:28:37', 'ừ gà', 1, 0, 0),
(21, 7, 3, 'Học quản trị cùng chú Lâm', 'Chú Lâm dễ thương há', '2026-04-13 00:00:00', 'High', 'In Progress', NULL, '2026-04-11 03:14:43', '2026-04-11 09:26:31', NULL, 0, 0, 0),
(22, 12, 3, 'fghjk', '', '2026-04-23 00:00:00', 'Medium', 'To Do', NULL, '2026-04-11 08:31:35', '2026-04-11 14:05:12', NULL, 1, 0, 0),
(23, 11, 3, 'fghjkdfghj', 'zxcvbn', '2026-04-13 00:00:00', 'High', 'To Do', NULL, '2026-04-11 08:34:34', '2026-04-13 01:09:19', NULL, 1, 0, 0),
(24, 13, 3, 'sdfsfdsdfdsfsd', 'sdfsdfsdf', '2026-04-13 00:00:00', 'High', 'To Do', NULL, '2026-04-11 09:34:38', '2026-04-11 09:34:38', NULL, 0, 0, 0),
(25, 14, 3, 'Sub 1', '', '2026-04-15 00:00:00', 'Medium', 'Pending', NULL, '2026-04-11 09:53:12', '2026-04-11 14:27:24', NULL, 0, 0, 1),
(26, 13, 3, 'đói vndajkv', 'fghjkl', '0000-00-00 00:00:00', 'Medium', 'Pending', NULL, '2026-04-11 09:57:14', '2026-04-14 23:04:43', NULL, 0, 0, 0),
(27, 13, 3, 'đói vndajkv', 'fghjkl', '0000-00-00 00:00:00', 'Medium', 'Pending', NULL, '2026-04-11 09:57:16', '2026-04-12 21:36:16', NULL, 0, 0, 0),
(28, 13, 3, 'đói vndajkv', 'fghjkl', '0000-00-00 00:00:00', 'Medium', 'In Progress', NULL, '2026-04-11 09:57:17', '2026-04-12 21:23:41', NULL, 0, 0, 0),
(29, 13, 3, 'đói vndajkv', 'fghjkl', '2026-04-30 00:00:00', 'Medium', 'To Do', NULL, '2026-04-11 09:57:17', '2026-04-14 16:05:36', NULL, 0, 0, 0),
(30, 15, 3, 'cgvhbjnk', 'xdfgchjk', '2026-04-30 00:00:00', 'Medium', 'To Do', NULL, '2026-04-11 14:03:51', '2026-04-11 14:03:51', NULL, 0, 0, 0),
(31, 14, 3, 'sub 2', 'lẹ mày', '2026-04-14 00:00:00', 'High', 'Done', NULL, '2026-04-11 14:07:17', '2026-04-11 14:16:57', 'ngu', 0, 0, 1),
(32, 16, 3, 'Khảo sát thị trường', 'Đi khảo sát thị trường công nghệ hiện nay', '2026-04-19 00:00:00', 'High', 'Done', NULL, '2026-04-11 15:19:21', '2026-04-11 15:23:00', NULL, 0, 0, 1),
(33, 16, 3, 'Khảo sát người dùng', 'Đi khảo sát thị trường công nghệ ET', '2026-04-19 00:00:00', 'Medium', 'Done', NULL, '2026-04-11 15:19:21', '2026-04-11 15:25:11', NULL, 0, 0, 1),
(34, 16, 3, 'Tổng kết kết quả nghiên cứu', 'Kết quả nghiên cứu như thế nào?', '2026-04-19 00:00:00', 'Medium', 'Done', NULL, '2026-04-11 15:19:21', '2026-04-11 15:26:33', NULL, 0, 0, 1),
(35, 17, 3, 'áaf', 'âfas', '2026-04-19 00:00:00', 'Medium', 'Pending', NULL, '2026-04-15 08:55:42', '2026-04-15 09:58:16', NULL, 0, 0, 1),
(36, 18, 3, 'Ngu lắm con', '', '2026-04-16 00:00:00', 'Medium', 'To Do', NULL, '2026-04-15 09:03:36', '2026-04-15 09:03:36', NULL, 0, 0, 0),
(38, 20, 2, 'zxvvx', '', '0000-00-00 00:00:00', 'Medium', 'To Do', NULL, '2026-04-15 09:15:43', '2026-04-15 09:15:43', NULL, 0, 0, 0);

-- --------------------------------------------------------

--
-- Table structure for table `subtask_attachments`
--

CREATE TABLE `subtask_attachments` (
  `id` int(11) NOT NULL,
  `subtask_id` int(11) NOT NULL,
  `file_name` varchar(255) NOT NULL,
  `file_url` varchar(500) NOT NULL,
  `uploaded_at` datetime DEFAULT current_timestamp(),
  `notes` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `subtask_attachments`
--

INSERT INTO `subtask_attachments` (`id`, `subtask_id`, `file_name`, `file_url`, `uploaded_at`, `notes`) VALUES
(1, 1, 'img.png', 'url', '2026-04-09 12:07:17', 'test'),
(2, 2, 'Note/Link', '', '2026-04-09 12:08:41', 'ùa hahaha'),
(3, 2, 'Note/Link', '', '2026-04-09 12:09:25', 'dạ'),
(4, 2, 'Note/Link', '', '2026-04-09 12:13:18', 'dạ em chào sếp'),
(5, 4, 'php-A188.png', 'https://i.ibb.co/sJtFP2Jn/php-A188.png', '2026-04-09 12:17:56', 'có cái cc'),
(6, 6, 'Note/Link', '', '2026-04-11 02:34:19', 'ưefhjskdhfksjdf'),
(7, 9, 'Note/Link', '', '2026-04-11 02:55:20', 'jhghjgj đạt ngu'),
(9, 21, 'Note/Link', '', '2026-04-11 09:26:45', 'dạaaaa'),
(10, 18, 'Note/Link', '', '2026-04-11 09:32:40', 'uk'),
(11, 21, 'Note/Link', '', '2026-04-11 09:39:36', 'dạ'),
(12, 31, 'Note/Link', '', '2026-04-11 14:07:47', 'dạ con chào chú'),
(13, 25, 'Note/Link', '', '2026-04-11 14:27:12', 'fwefwef'),
(14, 32, 'Note/Link', '', '2026-04-11 15:20:26', 'abcdxyz'),
(15, 33, 'Note/Link', '', '2026-04-11 15:20:39', 'đạt ngu lắm đừng khảo sát'),
(16, 34, 'Note/Link', '', '2026-04-11 15:20:47', 'chịu thua luôn'),
(17, 20, 'Note/Link', '', '2026-04-12 21:26:24', 'sdfghjk,'),
(18, 27, 'Note/Link', '', '2026-04-12 21:36:16', 'dssfsgffdfh'),
(19, 19, 'Note/Link', '', '2026-04-12 22:20:40', 'ggggfdh'),
(20, 26, 'Note/Link', '', '2026-04-14 23:04:43', 'bnm'),
(21, 26, 'Note/Link', '', '2026-04-14 23:04:48', 'bnm'),
(22, 35, 'Note/Link', '', '2026-04-15 09:57:31', 'fcgvhbjnkm');

-- --------------------------------------------------------

--
-- Table structure for table `tasks`
--

CREATE TABLE `tasks` (
  `id` int(11) NOT NULL,
  `department_id` int(11) NOT NULL,
  `created_by_user_id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `priority` enum('Low','Medium','High') DEFAULT 'Medium',
  `deadline` datetime DEFAULT NULL,
  `status` enum('To Do','In Progress','Done') DEFAULT 'To Do',
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `tasks`
--

INSERT INTO `tasks` (`id`, `department_id`, `created_by_user_id`, `title`, `description`, `priority`, `deadline`, `status`, `created_at`) VALUES
(1, 3, 1, 'Dự án Hệ thống Relioo', NULL, 'Medium', NULL, 'Done', '2026-04-08 22:54:32'),
(2, 1, 1, 'cxzzxc', 'zxczxczxc', 'Medium', '0000-00-00 00:00:00', 'In Progress', '2026-04-09 09:49:34'),
(3, 1, 1, 'Đi ngủ', 'Đi ngủ đi', 'Low', '2026-04-22 00:00:00', 'Done', '2026-04-11 00:57:59'),
(4, 3, 2, 'THIẾT KẾ GIAO DIỆN RELIOO', 'màu hường hiện đại dễ nhìn hì, có th ngu Phát đàn đắm chìm trong tình iu đừn như Phát nhé', 'High', '2026-12-04 00:00:00', 'To Do', '2026-04-11 02:53:55'),
(7, 3, 2, 'Làm IT ngu', 'Vô cái lớp toàn IT ngu', 'Medium', '2026-04-12 00:00:00', 'In Progress', '2026-04-11 03:08:45'),
(8, 3, 2, 'IT ngu', 'Vào lớp toàn IT ngu', 'High', '2026-04-12 00:00:00', 'To Do', '2026-04-11 03:10:34'),
(9, 3, 2, 'fghjkl', '', 'Medium', '2026-04-23 00:00:00', 'To Do', '2026-04-11 03:20:44'),
(10, 3, 2, 'fghjkl;\'jv', '', 'Medium', '0000-00-00 00:00:00', 'To Do', '2026-04-11 03:22:07'),
(11, 3, 2, 'édfgjkl', '', 'Medium', '0000-00-00 00:00:00', 'To Do', '2026-04-11 08:22:35'),
(12, 3, 2, 'ĂN SÁNG', 'bún bòa', 'High', '2026-04-02 00:00:00', 'To Do', '2026-04-11 08:31:35'),
(13, 3, 2, 'sdfsdfsdf', 'sdfsdfsdf', 'Medium', '0000-00-00 00:00:00', 'To Do', '2026-04-11 09:34:37'),
(14, 3, 2, 'Scroll Test Task', 'Testing horizontal scroll.', 'Medium', '2026-04-20 00:00:00', 'To Do', '2026-04-11 09:53:12'),
(15, 1, 1, 'cfgvhbjnkml', 'fghjl', 'Medium', '0000-00-00 00:00:00', 'To Do', '2026-04-11 14:03:51'),
(16, 3, 2, 'Khai phá công nghệ, nâng tầm kinh tế', 'nghiên cứu và phát triển ET Ngu', 'High', '2026-04-19 00:00:00', 'Done', '2026-04-11 15:19:21'),
(17, 1, 1, 'dâdadwdad', 'afaafasfasfafaf', 'Medium', '2026-04-22 00:00:00', 'To Do', '2026-04-15 08:55:42'),
(18, 1, 1, 'cxzzxc', 'dạ chú', 'Medium', '0000-00-00 00:00:00', 'To Do', '2026-04-15 09:03:36'),
(20, 1, 1, 'szvs', 'vzvzxvv', 'Medium', '0000-00-00 00:00:00', 'To Do', '2026-04-15 09:15:43');

-- --------------------------------------------------------

--
-- Table structure for table `task_reports`
--

CREATE TABLE `task_reports` (
  `id` int(11) NOT NULL,
  `subtask_id` int(11) DEFAULT NULL,
  `task_id` int(11) DEFAULT NULL,
  `content` text NOT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `q1_answer` text DEFAULT NULL,
  `q2_answer` text DEFAULT NULL,
  `q3_answer` text DEFAULT NULL,
  `ai_generated_content` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `task_reports`
--

INSERT INTO `task_reports` (`id`, `subtask_id`, `task_id`, `content`, `created_at`, `q1_answer`, `q2_answer`, `q3_answer`, `ai_generated_content`) VALUES
(1, 32, 16, 'Khảo sát thị trường Report', '2026-04-11 15:23:00', 'Tôi đã điều tra thị trường trà sữa', 'Trà sữa thì ngon, mà ET thì ngu', 'Không cho ET uống trà sữa', '\"Báo Cáo Tiến Độ: Khảo Sát Thị Trường\n\nMới đây, chúng tôi đã tiến hành khảo sát thị trường công nghệ hiện nay. Trong khuôn khổ dự án, chúng tôi đã thực hiện điều tra thị trường trà sữa.\n\nQua quá trình khảo sát, chúng tôi đã thu thập được những thông tin quý giá về thị trường trà sữa. Tuy nhiên, quá trình thực hiện cũng cho chúng tôi một số kinh nghiệm rút ra về cách quản lý và phối hợp trong nhóm.\n\nChúng tôi sẽ tiếp tục cải thiện và hoàn thiện quy trình khảo sát để mang lại kết quả tốt hơn trong tương lai. Cảm ơn sự hỗ trợ và hợp tác của tất cả các thành viên trong nhóm.\"'),
(2, 33, 16, 'Khảo sát người dùng Report', '2026-04-11 15:25:11', 'ăn cơm ngon', 'ghi nhận', 'ngu', '\"Báo cáo tiến độ: Khảo sát người dùng\n\nChúng tôi đã hoàn thành khảo sát thị trường công nghệ ET. Quá trình thực hiện đã giúp chúng tôi tích lũy kinh nghiệm và ghi nhận những thông tin quan trọng. Chúng tôi sẽ tiếp tục cải thiện và hoàn thiện quy trình cho những dự án tương lai.\n\n#KhảoSátThịTrường #CôngNghệET #TiếnĐộDựÁn\"'),
(3, 34, 16, 'Tổng kết kết quả nghiên cứu Report', '2026-04-11 15:26:33', 'công nghệ', 'dữ liệu', 'web 1', '\"Tổng kết kết quả nghiên cứu: Chúng tôi đã áp dụng công nghệ để thực hiện nghiên cứu và đạt được những kết quả đáng kể. Qua quá trình này, chúng tôi đã tích lũy được kinh nghiệm quý giá về việc thu thập và phân tích dữ liệu. Để cải thiện trong tương lai, chúng tôi sẽ tập trung vào việc nâng cao kỹ năng khai thác thông tin trên các nền tảng như web. Cảm ơn sự hỗ trợ và hợp tác!\" #Tổng_kết_nghiên_cứu #Kết_quả_thành_công'),
(4, NULL, 16, 'Khai phá công nghệ, nâng tầm kinh tế Tổng kết (AI)', '2026-04-11 15:27:15', NULL, NULL, NULL, 'Dự án \"Khai phá công nghệ, nâng tầm kinh tế\" của chúng ta đã đạt được những kết quả đáng kể và đầy ấn tượng! Tôi muốn dành một chút thời gian để tổng kết và biểu dương toàn bộ đội ngũ đã làm việc không ngừng nghỉ để mang lại thành công cho dự án này.\n\nTrước hết, chúng ta đã tiến hành một cuộc khảo sát thị trường công nghệ ET và trà sữa, thu thập được những thông tin quý giá về thị trường và tích lũy kinh nghiệm về cách quản lý và phối hợp trong nhóm. Quá trình này không chỉ giúp chúng ta hiểu rõ hơn về thị trường mà còn giúp chúng ta cải thiện và hoàn thiện quy trình khảo sát cho những dự án tương lai.\n\nTiếp theo, chúng ta đã áp dụng công nghệ để thực hiện nghiên cứu và đạt được những kết quả đáng kể. Qua quá trình này, chúng ta đã tích lũy được kinh nghiệm quý giá về việc thu thập và phân tích dữ liệu, và đã xác định được hướng cải thiện trong tương lai, đó là nâng cao kỹ năng khai thác thông tin trên các nền tảng như web.\n\nTôi muốn biểu dương toàn bộ đội ngũ vì sự nỗ lực và hợp tác không ngừng nghỉ. Mỗi thành viên trong nhóm đã đóng góp một phần quan trọng vào thành công của dự án, và tôi rất tự hào về kết quả mà chúng ta đã đạt được.\n\nDự án \"Khai phá công nghệ, nâng tầm kinh tế\" không chỉ là một dự án thành công, mà còn là một bước tiến quan trọng trong việc nâng cao kỹ năng và kiến thức của chúng ta. Chúng ta đã chứng minh rằng với sự hợp tác và nỗ lực, chúng ta có thể đạt được những kết quả đáng kể và đóng góp vào sự phát triển của công ty.\n\nCảm ơn tất cả các thành viên trong nhóm vì sự đóng góp và hợp tác. Chúng ta hãy tiếp tục làm việc cùng nhau để đạt được những thành công mới và nâng cao vị thế của công ty trong lĩnh vực công nghệ! #KhaiPháCôngNghệ #NângTầmKinhTế #ThànhCông #ĐộiNgũ'),
(5, NULL, 16, 'Khai phá công nghệ, nâng tầm kinh tế Tổng kết (AI)', '2026-04-11 16:00:24', NULL, NULL, NULL, 'Dự án \"Khai phá công nghệ, nâng tầm kinh tế\" của chúng ta đã đạt được những kết quả đáng kể và đầy ấn tượng! Tôi muốn dành một chút thời gian để tổng kết và biểu dương toàn bộ đội ngũ đã làm việc không ngừng nghỉ để mang lại thành công cho dự án này.\n\nTrước hết, chúng ta đã tiến hành một cuộc khảo sát thị trường công nghệ ET và trà sữa, thu thập được những thông tin quý giá về thị trường và tích lũy kinh nghiệm về cách quản lý và phối hợp trong nhóm. Quá trình này không chỉ giúp chúng ta hiểu rõ hơn về thị trường mà còn giúp chúng ta cải thiện và hoàn thiện quy trình khảo sát cho những dự án tương lai.\n\nTiếp theo, chúng ta đã áp dụng công nghệ để thực hiện nghiên cứu và đạt được những kết quả đáng kể. Qua quá trình này, chúng ta đã tích lũy được kinh nghiệm quý giá về việc thu thập và phân tích dữ liệu, và đã xác định được hướng cải thiện trong tương lai, đó là nâng cao kỹ năng khai thác thông tin trên các nền tảng như web.\n\nTôi muốn biểu dương toàn bộ đội ngũ vì sự nỗ lực và hợp tác không ngừng nghỉ. Mỗi thành viên trong nhóm đã đóng góp một phần quan trọng vào thành công của dự án, và tôi rất tự hào về kết quả mà chúng ta đã đạt được.\n\nDự án \"Khai phá công nghệ, nâng tầm kinh tế\" không chỉ là một dự án thành công, mà còn là một bước tiến quan trọng trong việc nâng cao kỹ năng và kiến thức của chúng ta. Chúng ta đã chứng minh rằng với sự hợp tác và nỗ lực, chúng ta có thể đạt được những kết quả đáng kể và đóng góp vào sự phát triển của công ty.\n\nCảm ơn tất cả các thành viên trong nhóm vì sự đóng góp và hợp tác. Hãy tiếp tục làm việc cùng nhau để đạt được những thành công mới và nâng cao vị thế của công ty trong lĩnh vực công nghệ! #KhaiPháCôngNghệ #NângTầmKinhTế #ThànhCông #ĐộiNgũ'),
(6, 19, 7, 'Thuyết trình cùng ba Hói Report', '2026-04-12 22:24:44', 'Công nghệ', 'toàn cầu', 'ok ha\n', '\"Báo cáo tiến độ: Thuyết trình cùng ba Hói\n\nChúng tôi đã hoàn thành việc thảo luận và thuyết trình cùng ba Hói. Quá trình thực hiện đã được hỗ trợ bởi công nghệ hiện đại, giúp tăng cường hiệu quả và chất lượng.\n\nMột số kinh nghiệm rút ra từ quá trình này là tầm quan trọng của việc áp dụng công nghệ vào công việc, giúp mở rộng phạm vi và khả năng tiếp cận toàn cầu.\n\nĐể cải thiện trong tương lai, chúng tôi sẽ tiếp tục áp dụng công nghệ và tinh chỉnh quy trình để đạt được kết quả tốt hơn. Cảm ơn sự hỗ trợ và hợp tác!\" #ThuyetTrinh #BaHoi #CongTy #TienDo');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `department_id` int(11) DEFAULT NULL,
  `role_id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `full_name` varchar(100) NOT NULL,
  `email` varchar(100) DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `avatar_url` varchar(255) DEFAULT NULL,
  `birthdate` varchar(20) DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `is_active` tinyint(1) DEFAULT 1,
  `cover_url` varchar(255) DEFAULT NULL,
  `location` varchar(100) DEFAULT NULL,
  `link_tiktok` varchar(255) DEFAULT NULL,
  `link_facebook` varchar(255) DEFAULT NULL,
  `link_instagram` varchar(255) DEFAULT NULL,
  `link_telegram` varchar(255) DEFAULT NULL,
  `hide_birthdate` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `department_id`, `role_id`, `username`, `password_hash`, `full_name`, `email`, `phone`, `avatar_url`, `birthdate`, `created_at`, `is_active`, `cover_url`, `location`, `link_tiktok`, `link_facebook`, `link_instagram`, `link_telegram`, `hide_birthdate`) VALUES
(1, 1, 1, 'ceo', '$2y$10$T5VDC/FVsL2dMWh6KLe//eJ6mwF2VcJo.AWSFi9Ln3.tzK7Tjnc6G', 'Nguyễn Văn CEO', 'ceo@relioo.com', '1278954563', 'https://i.ibb.co/xSc4hqvW/84d94cf812e2.jpg', NULL, '2026-04-08 22:54:32', 1, 'https://i.ibb.co/LDm5zV3R/663ecefcb0c8.jpg', 'Tỉnh Bà Rịa - Vũng Tàu', '', 'https://www.facebook.com/nguyen.long.314488', '', NULL, 1),
(2, 3, 2, 'leader_it', '$2y$10$T5VDC/FVsL2dMWh6KLe//eJ6mwF2VcJo.AWSFi9Ln3.tzK7Tjnc6G', 'Trần IT Trưởng', 'it@relioo.com', '0712351233', 'https://i.ibb.co/4nqmnKGw/3ca7340098e9.jpg', '2008-10-22', '2026-04-08 22:54:32', 1, 'https://i.ibb.co/ds7Wx7WV/00edeb3c8b2a.jpg', 'Thành phố Cần Thơ', '', 'https://www.facebook.com/tahpnart8', 'https://www.instagram.com/tahpnart8/', NULL, 0),
(3, 3, 3, 'staff_it1', '$2y$10$T5VDC/FVsL2dMWh6KLe//eJ6mwF2VcJo.AWSFi9Ln3.tzK7Tjnc6G', 'Vũ Nhân Viên 1', 'it1@relioo.com', '', 'https://i.ibb.co/x82MK65r/58c1a8dcde47.jpg', NULL, '2026-04-08 22:54:32', 1, 'https://i.ibb.co/wh77GZXc/7e3e92256b94.jpg', '', '', 'https://www.facebook.com/', '', '', 0);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `comments`
--
ALTER TABLE `comments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `parent_comment_id` (`parent_comment_id`),
  ADD KEY `idx_comments_post_date` (`post_id`,`created_at`);

--
-- Indexes for table `comment_reactions`
--
ALTER TABLE `comment_reactions`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_user_comment_reaction` (`comment_id`,`user_id`),
  ADD KEY `idx_comment_reactions_comment` (`comment_id`,`user_id`);

--
-- Indexes for table `conversations`
--
ALTER TABLE `conversations`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `conversation_members`
--
ALTER TABLE `conversation_members`
  ADD PRIMARY KEY (`conversation_id`,`user_id`),
  ADD KEY `idx_conv_members_user` (`user_id`,`conversation_id`);

--
-- Indexes for table `departments`
--
ALTER TABLE `departments`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `membership_requests`
--
ALTER TABLE `membership_requests`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `messages`
--
ALTER TABLE `messages`
  ADD PRIMARY KEY (`id`),
  ADD KEY `sender_id` (`sender_id`),
  ADD KEY `idx_messages_conv_date` (`conversation_id`,`created_at`);

--
-- Indexes for table `notifications`
--
ALTER TABLE `notifications`
  ADD PRIMARY KEY (`id`),
  ADD KEY `trigger_user_id` (`trigger_user_id`);

--
-- Indexes for table `notification_user`
--
ALTER TABLE `notification_user`
  ADD PRIMARY KEY (`notification_id`,`user_id`),
  ADD KEY `idx_noti_user_read` (`user_id`,`is_read`);

--
-- Indexes for table `posts`
--
ALTER TABLE `posts`
  ADD PRIMARY KEY (`id`),
  ADD KEY `author_id` (`author_id`),
  ADD KEY `task_report_id` (`task_report_id`),
  ADD KEY `idx_posts_visibility_date` (`visibility`,`created_at`),
  ADD KEY `idx_posts_dept_visibility` (`department_id`,`visibility`);

--
-- Indexes for table `post_edit_history`
--
ALTER TABLE `post_edit_history`
  ADD PRIMARY KEY (`id`),
  ADD KEY `post_id` (`post_id`);

--
-- Indexes for table `post_media`
--
ALTER TABLE `post_media`
  ADD PRIMARY KEY (`id`),
  ADD KEY `post_id` (`post_id`);

--
-- Indexes for table `post_reactions`
--
ALTER TABLE `post_reactions`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_user_post_reaction` (`post_id`,`user_id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `idx_post_reactions_post` (`post_id`,`user_id`);

--
-- Indexes for table `roles`
--
ALTER TABLE `roles`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `subtasks`
--
ALTER TABLE `subtasks`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_subtasks_assignee_status` (`assignee_id`,`status`),
  ADD KEY `idx_subtasks_task_status` (`task_id`,`status`);

--
-- Indexes for table `subtask_attachments`
--
ALTER TABLE `subtask_attachments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `subtask_id` (`subtask_id`);

--
-- Indexes for table `tasks`
--
ALTER TABLE `tasks`
  ADD PRIMARY KEY (`id`),
  ADD KEY `department_id` (`department_id`),
  ADD KEY `created_by_user_id` (`created_by_user_id`);

--
-- Indexes for table `task_reports`
--
ALTER TABLE `task_reports`
  ADD PRIMARY KEY (`id`),
  ADD KEY `subtask_id` (`subtask_id`),
  ADD KEY `task_id` (`task_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD KEY `department_id` (`department_id`),
  ADD KEY `role_id` (`role_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `comments`
--
ALTER TABLE `comments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=59;

--
-- AUTO_INCREMENT for table `comment_reactions`
--
ALTER TABLE `comment_reactions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `conversations`
--
ALTER TABLE `conversations`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `departments`
--
ALTER TABLE `departments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `membership_requests`
--
ALTER TABLE `membership_requests`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `messages`
--
ALTER TABLE `messages`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=124;

--
-- AUTO_INCREMENT for table `notifications`
--
ALTER TABLE `notifications`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=192;

--
-- AUTO_INCREMENT for table `posts`
--
ALTER TABLE `posts`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=23;

--
-- AUTO_INCREMENT for table `post_edit_history`
--
ALTER TABLE `post_edit_history`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `post_media`
--
ALTER TABLE `post_media`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `post_reactions`
--
ALTER TABLE `post_reactions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=163;

--
-- AUTO_INCREMENT for table `roles`
--
ALTER TABLE `roles`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `subtasks`
--
ALTER TABLE `subtasks`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=39;

--
-- AUTO_INCREMENT for table `subtask_attachments`
--
ALTER TABLE `subtask_attachments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=23;

--
-- AUTO_INCREMENT for table `tasks`
--
ALTER TABLE `tasks`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- AUTO_INCREMENT for table `task_reports`
--
ALTER TABLE `task_reports`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `comments`
--
ALTER TABLE `comments`
  ADD CONSTRAINT `comments_ibfk_1` FOREIGN KEY (`post_id`) REFERENCES `posts` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `comments_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `comments_ibfk_3` FOREIGN KEY (`parent_comment_id`) REFERENCES `comments` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `comment_reactions`
--
ALTER TABLE `comment_reactions`
  ADD CONSTRAINT `comment_reactions_ibfk_1` FOREIGN KEY (`comment_id`) REFERENCES `comments` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `conversation_members`
--
ALTER TABLE `conversation_members`
  ADD CONSTRAINT `conversation_members_ibfk_1` FOREIGN KEY (`conversation_id`) REFERENCES `conversations` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `conversation_members_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `messages`
--
ALTER TABLE `messages`
  ADD CONSTRAINT `messages_ibfk_1` FOREIGN KEY (`conversation_id`) REFERENCES `conversations` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `messages_ibfk_2` FOREIGN KEY (`sender_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `notifications`
--
ALTER TABLE `notifications`
  ADD CONSTRAINT `notifications_ibfk_1` FOREIGN KEY (`trigger_user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `notification_user`
--
ALTER TABLE `notification_user`
  ADD CONSTRAINT `notification_user_ibfk_1` FOREIGN KEY (`notification_id`) REFERENCES `notifications` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `notification_user_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `posts`
--
ALTER TABLE `posts`
  ADD CONSTRAINT `posts_ibfk_1` FOREIGN KEY (`author_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `posts_ibfk_2` FOREIGN KEY (`department_id`) REFERENCES `departments` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `posts_ibfk_3` FOREIGN KEY (`task_report_id`) REFERENCES `task_reports` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `post_edit_history`
--
ALTER TABLE `post_edit_history`
  ADD CONSTRAINT `post_edit_history_ibfk_1` FOREIGN KEY (`post_id`) REFERENCES `posts` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `post_media`
--
ALTER TABLE `post_media`
  ADD CONSTRAINT `post_media_ibfk_1` FOREIGN KEY (`post_id`) REFERENCES `posts` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `post_reactions`
--
ALTER TABLE `post_reactions`
  ADD CONSTRAINT `post_reactions_ibfk_1` FOREIGN KEY (`post_id`) REFERENCES `posts` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `post_reactions_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `subtasks`
--
ALTER TABLE `subtasks`
  ADD CONSTRAINT `subtasks_ibfk_1` FOREIGN KEY (`task_id`) REFERENCES `tasks` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `subtasks_ibfk_2` FOREIGN KEY (`assignee_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `subtask_attachments`
--
ALTER TABLE `subtask_attachments`
  ADD CONSTRAINT `subtask_attachments_ibfk_1` FOREIGN KEY (`subtask_id`) REFERENCES `subtasks` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `tasks`
--
ALTER TABLE `tasks`
  ADD CONSTRAINT `tasks_ibfk_1` FOREIGN KEY (`department_id`) REFERENCES `departments` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `tasks_ibfk_2` FOREIGN KEY (`created_by_user_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `task_reports`
--
ALTER TABLE `task_reports`
  ADD CONSTRAINT `task_reports_ibfk_1` FOREIGN KEY (`subtask_id`) REFERENCES `subtasks` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `task_reports_ibfk_2` FOREIGN KEY (`task_id`) REFERENCES `tasks` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `users`
--
ALTER TABLE `users`
  ADD CONSTRAINT `users_ibfk_1` FOREIGN KEY (`department_id`) REFERENCES `departments` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `users_ibfk_2` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
