-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: May 05, 2025 at 05:48 PM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `cocdb`
--
CREATE DATABASE IF NOT EXISTS `cocdb` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
USE `cocdb`;

-- --------------------------------------------------------

--
-- Table structure for table `admin`
--

CREATE TABLE `admin` (
  `admin_id` int(11) NOT NULL,
  `description` text DEFAULT NULL,
  `user_email` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `admin`
--

INSERT INTO `admin` (`admin_id`, `description`, `user_email`) VALUES
(2, NULL, 'adam@gmail.com'),
(3, '', 'ivan@gmail.com');

-- --------------------------------------------------------

--
-- Table structure for table `answer`
--

CREATE TABLE `answer` (
  `answer_id` int(11) NOT NULL,
  `answer` text NOT NULL,
  `accuracy` tinyint(1) NOT NULL,
  `question_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `answer`
--

INSERT INTO `answer` (`answer_id`, `answer`, `accuracy`, `question_id`) VALUES
(12, 'A. Speed up devices', 0, 6),
(13, 'B. Protect data and systems', 1, 6),
(14, 'C. Increase downloads', 0, 6),
(15, 'D. None of the above', 0, 6),
(16, 'A. Firewall', 0, 7),
(17, 'B. Antivirus', 0, 7),
(18, 'C. Ransomware ', 1, 7),
(19, 'D. HTTPS', 0, 7),
(20, 'A. Realistic-looking emails', 1, 8),
(21, 'B. Physical mail', 0, 8),
(22, 'C. Phone calls only', 0, 8),
(23, 'D. Web browsers', 0, 8),
(24, 'A. Just a password', 0, 9),
(25, 'B. Password + OTP', 1, 9),
(26, 'C. Username only', 0, 9),
(27, 'D. CAPTCHA', 0, 9),
(28, 'A. Trusted websites', 0, 10),
(29, 'B. Emails from friends', 0, 10),
(30, 'C. Suspicious emails', 1, 10),
(31, 'D. Your banks app', 0, 10),
(32, 'A. Using the same password', 0, 11),
(33, 'B. Writing passwords on your desk', 0, 11),
(34, 'C. Using strong, unique passwords', 1, 11),
(35, 'D. Sharing passwords', 0, 11),
(36, 'A. Connects you to the internet', 0, 12),
(37, 'B. Blocks unauthorized access', 1, 12),
(38, 'C. Displays ads', 0, 12),
(39, 'D. Deletes files', 0, 12),
(40, 'A. Equifax', 0, 13),
(41, 'B. Yahoo breach', 0, 13),
(42, 'C. WannaCry', 1, 13),
(43, 'D. iLoveYou', 0, 13),
(44, 'A. Slow down computers', 0, 14),
(45, 'B. Back up photos', 0, 14),
(46, 'C. Detect and remove malware', 1, 14),
(47, 'D. Format hard drives', 0, 14),
(52, 'shallow copy', 1, 16),
(53, '[[99, 2], [3, 4]]', 1, 17);

-- --------------------------------------------------------

--
-- Table structure for table `applicant_document`
--

CREATE TABLE `applicant_document` (
  `document_id` int(11) NOT NULL,
  `directory` varchar(100) NOT NULL,
  `doc_name` varchar(100) NOT NULL,
  `applicant_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `applicant_document`
--

INSERT INTO `applicant_document` (`document_id`, `directory`, `doc_name`, `applicant_id`) VALUES
(1, 'applicant_document/6813972d052ef.pdf', 'Techlympics Math Whiz Champion Certificate - KHOO GUO HAO.pdf', 5),
(2, 'applicant_document/6813972d054c4.pdf', 'Techlympics Math Whiz Participation Certificate - KHOO GUO HAO.pdf', 2);

-- --------------------------------------------------------

--
-- Table structure for table `career`
--

CREATE TABLE `career` (
  `career_id` int(11) NOT NULL,
  `name` varchar(50) NOT NULL,
  `description` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `career`
--

INSERT INTO `career` (`career_id`, `name`, `description`) VALUES
(1, 'Front-End Web Developer', NULL),
(2, 'Back-End Web Developer', NULL),
(3, 'Cybersecurity Analyst', NULL),
(4, 'AI/ML Engineer', NULL),
(5, 'Full-Stack Developer', NULL),
(6, 'Mobile Game Developer', NULL),
(7, 'Data Analyst', NULL),
(8, 'Systems Architect', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `career_field_relation`
--

CREATE TABLE `career_field_relation` (
  `career_id` int(11) NOT NULL,
  `field_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `career_field_relation`
--

INSERT INTO `career_field_relation` (`career_id`, `field_id`) VALUES
(1, 1),
(1, 2),
(1, 3),
(1, 5),
(2, 5),
(2, 6),
(2, 7),
(2, 8),
(2, 10),
(3, 11),
(3, 12),
(3, 13),
(3, 15),
(4, 8),
(4, 17),
(4, 19),
(4, 20),
(4, 21),
(4, 22),
(5, 1),
(5, 2),
(5, 3),
(5, 5),
(5, 6),
(5, 7),
(5, 8),
(5, 9),
(5, 16),
(6, 4),
(6, 9),
(6, 23),
(7, 6),
(7, 8),
(7, 14),
(7, 20),
(7, 22),
(8, 9),
(8, 10),
(8, 11),
(8, 12),
(8, 15),
(8, 16);

-- --------------------------------------------------------

--
-- Table structure for table `career_preference`
--

CREATE TABLE `career_preference` (
  `student_id` int(11) NOT NULL,
  `career_id` int(11) NOT NULL,
  `experience` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `career_preference`
--

INSERT INTO `career_preference` (`student_id`, `career_id`, `experience`) VALUES
(5, 2, 'intermediate'),
(5, 3, 'beginner'),
(15, 1, 'intermediate'),
(15, 5, 'intermediate');

-- --------------------------------------------------------

--
-- Table structure for table `chapter`
--

CREATE TABLE `chapter` (
  `chapter_id` int(11) NOT NULL,
  `chapter_title` varchar(100) NOT NULL,
  `course_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `chapter`
--

INSERT INTO `chapter` (`chapter_id`, `chapter_title`, `course_id`) VALUES
(3, 'Introduction and Basic Concepts of AI', 7),
(4, 'Expert System', 7),
(6, 'Overview of Client and Server', 1),
(7, 'Chapter 1: Introduction to Cybersecurity', 8),
(8, 'Chapter 2: Protecting Your Devices', 8),
(9, 'Chapter 3: Cybersecurity in Practice', 8),
(10, 'Object-Oriented Programming in Python', 5),
(11, 'Pythonic Features and Advanced Techniques', 5),
(12, 'Real-World Applications & Quiz', 5);

-- --------------------------------------------------------

--
-- Table structure for table `content`
--

CREATE TABLE `content` (
  `content_id` int(11) NOT NULL,
  `content` text NOT NULL,
  `section_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `content`
--

INSERT INTO `content` (`content_id`, `content`, `section_id`) VALUES
(1, '', 11),
(2, '<h2><strong>What is Client-Server Architecture?</strong></h2>\r\n<p>Client-Server Architecture is a network model where tasks or workloads are divided between two main types of devices: clients and servers.</p>\r\n<p>The client is a device (like a computer, smartphone, or browser) that requests services or resources.</p>\r\n<p>The server is a powerful system that provides those services or resources to clients.</p>\r\n<p>This model is the backbone of how the internet and most web applications work.</p>\r\n<p>&nbsp;</p>\r\n<h2><strong>How It Works:</strong></h2>\r\n<p>The client sends a request to the server.</p>\r\n<p>The server processes the request.</p>\r\n<p>The server sends back a response (such as a web page, file, or data).</p>\r\n<p>For example, when you open a website:</p>\r\n<p>Your browser (client) requests the webpage.</p>\r\n<p>The web server sends the page content back.</p>\r\n<p>&nbsp;</p>\r\n<h2>Key Features:</h2>\r\n<p>Separation of Roles: Clients handle user interaction; servers handle data processing and storage.</p>\r\n<p>Centralized Resources: Data and logic are stored on the server, not on each client.</p>\r\n<p>Scalability: Servers can handle many clients simultaneously.</p>\r\n<p>Security: Centralized control allows better protection and monitoring.</p>\r\n<p>&nbsp;</p>\r\n<h3>Common Examples:</h3>\r\n<p>Web Browsing: Browser (client) &harr; Web server (like Apache or Nginx)</p>\r\n<p>Email: Email client (like Outlook) &harr; Mail server (like Microsoft Exchange)</p>\r\n<p>Database Access: Application (client) &harr; Database server (like MySQL)</p>\r\n<p>Visual Analogy:<br>Think of a restaurant:</p>\r\n<p>Client = customer ordering food</p>\r\n<p>Server = kitchen preparing the food</p>\r\n<p>The waiter delivers the food (the response) to the customer.</p>', 12),
(3, '<p>&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;<iframe src=\"https://www.youtube.com/embed/inWWhr5tnEA?si=mo9ip5z_QpWAEBQc\" width=\"560\" height=\"314\" allowfullscreen=\"allowfullscreen\"></iframe></p>\r\n<p>&nbsp;</p>', 14),
(4, '<p style=\"padding-left: 80px;\"><iframe src=\"https://www.youtube.com/embed/TjgCkfCps30?si=Djnq6p0n3PGyUFUH\" width=\"560\" height=\"314\" allowfullscreen=\"allowfullscreen\"></iframe></p>', 15),
(5, '<p style=\"padding-left: 80px;\">&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;<img src=\"https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcRqjGEpQWbARrBh4AFemXbkxRHv_5t0XHsZ_g&amp;s\" alt=\"\" width=\"90\" height=\"90\">&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;<iframe src=\"https://www.youtube.com/embed/Dk-ZqQ-bfy4?si=OSqpAgv7y62MqbOz\" width=\"560\" height=\"314\" allowfullscreen=\"allowfullscreen\"></iframe></p>', 16),
(6, '<p style=\"padding-left: 80px;\"><iframe src=\"https://www.youtube.com/embed/aO858HyFbKI?si=70oMHApGmDtCIITV\" width=\"560\" height=\"314\" allowfullscreen=\"allowfullscreen\"></iframe></p>', 18),
(7, '<p style=\"padding-left: 80px;\"><iframe src=\"https://www.youtube.com/embed/kDEX1HXybrU?si=MR03CJXf_nyoVe5Z\" width=\"560\" height=\"314\" allowfullscreen=\"allowfullscreen\"></iframe></p>', 20),
(8, '<p style=\"padding-left: 80px;\"><iframe src=\"https://www.youtube.com/embed/PKHH_gvJ_hA?si=g3sQXXv7rXljN1YS\" width=\"560\" height=\"314\" allowfullscreen=\"allowfullscreen\"></iframe></p>', 21),
(9, '<p>&nbsp; &nbsp;</p>\r\n<h3 data-start=\"507\" data-end=\"723\">Object-Oriented Programming (OOP) is a programming paradigm based on the concept of objects,which can contain data and code: data in the form of fields (attributes), and code in the form of methods.</h3>\r\n<p class=\"\" data-start=\"725\" data-end=\"850\">In Python, classes are defined using the class keyword, and objects are instances of these classes. Heres a basic example:</p>\r\n<div class=\"contain-inline-size rounded-md border-[0.5px] border-token-border-medium relative bg-token-sidebar-surface-primary\">&nbsp;</div>\r\n<p class=\"\" data-start=\"1106\" data-end=\"1196\">This structure allows us to bundle behavior and data, and build reusable, modular systems.</p>', 23),
(10, '<p>&nbsp; &nbsp;</p>\r\n<h1 data-start=\"1995\" data-end=\"2039\">Subtopic 1: Decorators and Generators</h1>\r\n<p class=\"\" data-start=\"2041\" data-end=\"2221\">As Python programmers become more advanced, they begin to use powerful features that make their code more efficient and expressive. Two such features are decorators and generators.</p>\r\n<p class=\"\" data-start=\"2223\" data-end=\"2552\">Decorators are a unique aspect of Python that allow a function&rsquo;s behavior to be modified or extended without changing its code. This makes decorators useful for logging, access control, or instrumentation. They wrap a function in another layer of logic, giving you reusable tools to enhance functionality with minimal repetition.</p>\r\n<p class=\"\" data-start=\"2554\" data-end=\"2870\">Generators, on the other hand, provide an elegant way to work with sequences of data in a memory-efficient manner. Rather than loading all data at once, generators yield one item at a time, making them ideal for large datasets or infinite loops. These tools make advanced Python code both cleaner and more efficient.</p>', 24),
(11, '<p>&nbsp; &nbsp;By learning how to interact with APIs, students can fetch real-time data from platforms like weather services, social media, or news aggregators. This unlocks enormous potential for automation, app development, and data-driven decision-making. Understanding APIs also introduces key web concepts such as status codes, JSON parsing, and endpoint structures.</p>', 25),
(12, '<p>&nbsp; &nbsp;</p>\r\n<p class=\"\" data-start=\"1173\" data-end=\"1507\">Inheritance allows a class (known as a child or subclass) to inherit properties and behaviors from another class (known as a parent or superclass). This means that the child class automatically possesses all the functionality of the parent unless explicitly overridden. This reduces code repetition and makes systems easier to manage.</p>\r\n<p class=\"\" data-start=\"1509\" data-end=\"1924\">Polymorphism, another key OOP concept, refers to the ability of different object types to be accessed through the same interface. It enables a single function or method to behave differently depending on the context or the object calling it. Together, inheritance and polymorphism bring powerful flexibility and extendability to Python programs, allowing developers to build complex systems with minimal redundancy.</p>', 26),
(13, '<p>&nbsp; &nbsp;</p>\r\n<h3 data-start=\"4442\" data-end=\"4656\">Data serialization refers to converting Python objects into a format that can be stored or transmitted and reconstructed later. Python supports multiple serialization formats, the most common being JSON and Pickle.</h3>\r\n<p class=\"\" data-start=\"4658\" data-end=\"5083\">JSON (JavaScript Object Notation) is a lightweight and human-readable format used for data exchange, especially between web applications. Pickle, while more powerful for Python-specific objects, is less secure and less portable but extremely useful for internal projects. Mastering these formats allows developers to build programs that remember user settings, cache data, or transmit information between systems effectively.</p>', 27),
(14, '<p>&nbsp; &nbsp;</p>\r\n<p class=\"\" data-start=\"2939\" data-end=\"3229\">Resource management is a common concern in programming. Whether yoworking with files, network connections, or databases, rucial to ensure that resources are properly acquired and released. Pythoolution to this is the context manager, commonly used with&nbsp;tatement.</p>\r\n<p class=\"\" data-start=\"3231\" data-end=\"3594\">Thatement provides a clean and readable way to handle setup and teardown operations automatically. This ensures that resources are not leaked and that your programs remain reliable and maintainable. Understanding context managers also opens the door to writing your own custom resource handlers, further advancing your mastery of Pythapabilities.</p>', 28);

-- --------------------------------------------------------

--
-- Table structure for table `course`
--

CREATE TABLE `course` (
  `course_id` int(11) NOT NULL,
  `update_date` datetime NOT NULL,
  `publish_date` datetime DEFAULT NULL,
  `status` varchar(50) NOT NULL,
  `removal_reason` text DEFAULT NULL,
  `lecturer_id` int(11) NOT NULL,
  `proposal_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `course`
--

INSERT INTO `course` (`course_id`, `update_date`, `publish_date`, `status`, `removal_reason`, `lecturer_id`, `proposal_id`) VALUES
(1, '2025-04-11 13:44:48', '2025-04-11 15:44:48', 'Published', NULL, 1, 1),
(4, '2025-04-05 09:00:00', '2025-04-01 10:00:00', 'Published', NULL, 2, 4),
(5, '2025-04-06 14:00:00', '2025-04-02 11:00:00', 'Published', NULL, 2, 5),
(6, '2025-04-07 13:00:00', '2025-04-03 12:00:00', 'Published', NULL, 2, 6),
(7, '2025-04-08 16:00:00', '2025-04-04 15:00:00', 'Published', NULL, 2, 7),
(8, '2025-04-14 10:30:00', '2025-04-16 09:00:00', 'Published', NULL, 1, 8),
(9, '2025-04-15 11:00:00', '2025-04-17 10:30:00', 'Published', NULL, 1, 9),
(10, '2025-04-16 14:00:00', '2025-04-18 12:45:00', 'Published', NULL, 2, 10),
(11, '2025-04-17 16:00:00', '2025-04-19 14:15:00', 'Published', NULL, 2, 11),
(12, '0000-00-00 00:00:00', '2025-05-05 23:26:17', 'Published', NULL, 1, 14),
(13, '0000-00-00 00:00:00', '2025-05-05 23:26:19', 'Published', NULL, 1, 13),
(14, '0000-00-00 00:00:00', '2025-05-05 23:26:21', 'Published', NULL, 1, 12);

-- --------------------------------------------------------

--
-- Table structure for table `course_enrolment`
--

CREATE TABLE `course_enrolment` (
  `enrol_id` int(11) NOT NULL,
  `last_access_date` datetime NOT NULL,
  `rating` tinyint(4) DEFAULT NULL,
  `review_title` varchar(100) DEFAULT NULL,
  `review_comment` text DEFAULT NULL,
  `review_date` datetime DEFAULT NULL,
  `course_id` int(11) NOT NULL,
  `student_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `course_enrolment`
--

INSERT INTO `course_enrolment` (`enrol_id`, `last_access_date`, `rating`, `review_title`, `review_comment`, `review_date`, `course_id`, `student_id`) VALUES
(19, '2025-05-05 22:07:12', NULL, NULL, NULL, NULL, 1, 5);

-- --------------------------------------------------------

--
-- Table structure for table `course_field`
--

CREATE TABLE `course_field` (
  `proposal_id` int(11) NOT NULL,
  `field_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `course_field`
--

INSERT INTO `course_field` (`proposal_id`, `field_id`) VALUES
(1, 1),
(1, 2),
(1, 3),
(1, 5),
(1, 6),
(1, 7),
(4, 1),
(4, 5),
(4, 6),
(4, 7),
(4, 8),
(4, 16),
(5, 5),
(5, 8),
(5, 9),
(5, 14),
(6, 14),
(6, 17),
(6, 19),
(6, 20),
(6, 21),
(6, 22),
(8, 12),
(9, 12),
(10, 12),
(10, 13),
(11, 11),
(11, 12),
(11, 13),
(11, 23),
(12, 11),
(12, 12),
(13, 12),
(13, 13),
(13, 17),
(13, 19),
(14, 3),
(14, 4),
(14, 6),
(14, 7),
(14, 8),
(14, 11),
(14, 12),
(14, 13),
(15, 1),
(15, 2),
(15, 3),
(15, 5),
(15, 6),
(15, 7),
(15, 9),
(15, 10),
(15, 15),
(15, 16),
(16, 4),
(16, 6),
(16, 8),
(16, 10),
(16, 14),
(16, 17),
(16, 19),
(16, 20),
(16, 21),
(16, 22);

-- --------------------------------------------------------

--
-- Table structure for table `course_proposal`
--

CREATE TABLE `course_proposal` (
  `proposal_id` int(11) NOT NULL,
  `title` varchar(100) NOT NULL,
  `cover_img_url` varchar(100) NOT NULL DEFAULT 'img/defaultCourse.jpg',
  `course_style` varchar(30) NOT NULL,
  `difficulty` varchar(30) NOT NULL,
  `scope` text NOT NULL,
  `objective` text NOT NULL,
  `description` text NOT NULL,
  `completion_time` int(11) NOT NULL,
  `submit_date` datetime NOT NULL,
  `approval_date` datetime NOT NULL DEFAULT current_timestamp(),
  `approval_status` varchar(30) NOT NULL,
  `lecturer_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `course_proposal`
--

INSERT INTO `course_proposal` (`proposal_id`, `title`, `cover_img_url`, `course_style`, `difficulty`, `scope`, `objective`, `description`, `completion_time`, `submit_date`, `approval_date`, `approval_status`, `lecturer_id`) VALUES
(1, 'Develop Your First Website with No Coding Experience', 'img/murdock.jpg', 'Visual-Based', 'beginner', 'Lorem ipsum dolor sit amet consectetur adipisicing elit. Doloribus, explicabo fugiat tempora modi consectetur sint deserunt quis blanditiis animi nostrum quos fuga officiis similique nisi ut voluptas tenetur iusto. Mollitia doloribus at deserunt quae quod temporibus, repellendus fugit saepe. Nemo quibusdam repellat inventore, quos repellendus nobis quas illum culpa corrupti reiciendis est omnis provident voluptate placeat molestias eligendi animi perspiciatis natus? Inventore vitae similique provident ducimus doloremque obcaecati, animi maiores. Minus in ipsam quas error sunt quaerat cumque, placeat esse eligendi tenetur hic? Officiis cupiditate sit ratione cumque. Dicta quidem sunt quam temporibus suscipit et, quo cumque iusto. Distinctio, quod.', 'Lorem ipsum dolor sit amet consectetur adipisicing elit. Doloribus, explicabo fugiat tempora modi consectetur sint deserunt quis blanditiis animi nostrum quos fuga officiis similique nisi ut voluptas tenetur iusto. Mollitia doloribus at deserunt quae quod temporibus, repellendus fugit saepe. Nemo quibusdam repellat inventore, quos repellendus nobis quas illum culpa corrupti reiciendis est omnis provident voluptate placeat molestias eligendi animi perspiciatis natus? Inventore vitae similique provident ducimus doloremque obcaecati, animi maiores. Minus in ipsam quas error sunt quaerat cumque, placeat esse eligendi tenetur hic? Officiis cupiditate sit ratione cumque. Dicta quidem sunt quam temporibus suscipit et, quo cumque iusto. Distinctio, quod.', 'Lorem ipsum dolor sit amet consectetur adipisicing elit. Doloribus, explicabo fugiat tempora modi consectetur sint deserunt quis blanditiis animi nostrum quos fuga officiis similique nisi ut voluptas tenetur iusto. Mollitia doloribus at deserunt quae quod temporibus, repellendus fugit saepe. Nemo quibusdam repellat inventore, quos repellendus nobis quas illum culpa corrupti reiciendis est omnis provident voluptate placeat molestias eligendi animi perspiciatis natus? Inventore vitae similique provident ducimus doloremque obcaecati, animi maiores. Minus in ipsam quas error sunt quaerat cumque, placeat esse eligendi tenetur hic? Officiis cupiditate sit ratione cumque. Dicta quidem sunt quam temporibus suscipit et, quo cumque iusto. Distinctio, quod.', 30, '2025-04-01 00:00:00', '2025-05-04 18:56:01', 'Approved', 1),
(4, 'Introduction to Web Development', 'img/defaultCourse.jpg', 'Visual-Based', 'beginner', '<ul><li>Learn HTML and CSS</li><li>Build responsive websites</li><li>Introduction to JavaScript</li></ul>', '<ul><li>Understand web fundamentals</li><li>Apply coding practices</li><li>Build your first website</li></ul>', 'This course covers the basics of web development, including HTML, CSS, and JavaScript. Students will create their first website by the end of the course.', 30, '2025-03-30 08:00:00', '2025-05-04 18:56:01', 'Approved', 2),
(5, 'Advanced Python Programming', 'img/pythonLogo.png', 'Mixed-Based', 'advanced', '<ul><li>Master Python programming</li><li>Work with data structures and algorithms</li><li>Build complex projects</li></ul>', '<ul><li>Enhance your coding skills in Python</li><li>Learn object-oriented programming</li><li>Develop practical applications</li></ul>', 'This advanced course focuses on Python programming for experienced developers. Topics include advanced data structures, algorithms, and building real-world applications.', 45, '2025-03-31 10:30:00', '2025-05-04 18:56:01', 'Approved', 2),
(6, 'Data Science with R', 'img/defaultCourse.jpg', 'Mixed-Based', 'intermediate', '<ul><li>Data analysis using R</li><li>Visualize data effectively</li><li>Build predictive models</li></ul>', '<ul><li>Learn data manipulation and cleaning</li><li>Apply statistical techniques</li><li>Create data visualizations</li></ul>', 'This course introduces R for data science. It covers data analysis, visualization, and machine learning, aimed at turning raw data into insights.', 40, '2025-04-01 09:00:00', '2025-05-04 18:56:01', 'Approved', 2),
(7, 'Introduction to Artificial Intelligence', 'img/defaultCourse.jpg', 'Text-Based', 'beginner', '<ul><li>Explore AI concepts and techniques</li><li>Understand machine learning algorithms</li><li>Apply AI in real-world scenarios</li></ul>', '<ul><li>Study neural networks and deep learning</li><li>Implement AI models in Python</li><li>Work with AI frameworks</li></ul>', 'This course covers the fundamentals of Artificial Intelligence, from machine learning to deep learning. It prepares students to create AI-based solutions for modern problems.', 50, '2025-04-02 14:00:00', '2025-05-04 18:56:01', 'Approved', 2),
(8, 'Cybersecurity Essentials', 'img/defaultCourse.jpg', 'Text-Based', 'beginner', '<ul><li>Understand threats and vulnerabilities</li><li>Learn cryptography basics</li><li>Identify network security layers</li></ul>', '<ul><li>Build a strong security foundation</li><li>Protect data and systems</li><li>Recognize and respond to attacks</li></ul>', 'This course provides a foundational understanding of cybersecurity principles, including threat types, secure practices, and basic encryption techniques.', 35, '2025-04-10 09:00:00', '2025-05-04 18:56:01', 'Approved', 1),
(9, 'Ethical Hacking & Penetration Testing', 'img/defaultCourse.jpg', 'Mixed-Based', 'intermediate', '<ul><li>Conduct vulnerability assessments</li><li>Explore penetration testing phases</li><li>Use tools like Kali Linux and Metasploit</li></ul>', '<ul><li>Simulate real-world attacks</li><li>Understand attacker mindsets</li><li>Strengthen system defenses</li></ul>', 'Students will learn how ethical hackers uncover weaknesses in systems through legal and structured penetration testing methodologies.', 45, '2025-04-11 13:00:00', '2025-05-04 18:56:01', 'Approved', 1),
(10, 'Digital Forensics Fundamentals', 'img/defaultCourse.jpg', 'Text-Based', 'intermediate', '<ul><li>Introduction to computer forensics</li><li>Understand file system analysis</li><li>Preserve and recover evidence</li></ul>', '<ul><li>Gain skills in evidence handling</li><li>Explore digital investigation techniques</li><li>Learn about forensic software tools</li></ul>', 'This course introduces the core concepts of digital forensics, focusing on identifying, collecting, preserving, and analyzing digital evidence in legal investigations.', 48, '2025-04-12 10:00:00', '2025-05-04 18:56:01', 'Approved', 2),
(11, 'Advanced Mobile and Network Forensics', 'img/defaultCourse.jpg', 'Mixed-Based', 'advanced', '<ul><li>Analyze mobile device data</li><li>Investigate wireless traffic</li><li>Work with cloud forensic tools</li></ul>', '<ul><li>Deepen understanding of mobile and network forensics</li><li>Apply practical forensic methodologies</li><li>Trace cybercrimes in mobile and IoT environments</li></ul>', 'Targeted at experienced learners, this course explores the challenges and tools used in advanced mobile device analysis and network packet investigations.', 80, '2025-04-13 15:30:00', '2025-05-04 18:56:01', 'Approved', 2),
(12, 'Network Security Basics', 'img/1746458449_download.jpeg', 'Mixed', 'intermediate', '<ul>\r\n<li class=\"\" data-start=\"1495\" data-end=\"1547\">\r\n<p class=\"\" data-start=\"1497\" data-end=\"1547\">Introduction to network components and protocols</p>\r\n</li>\r\n<li class=\"\" data-start=\"1548\" data-end=\"1587\">\r\n<p class=\"\" data-start=\"1550\" data-end=\"1587\">Overview of network vulnerabilities</p>\r\n</li>\r\n<li class=\"\" data-start=\"1588\" data-end=\"1633\">\r\n<p class=\"\" data-start=\"1590\" data-end=\"1633\">Common attack types (e.g., DoS, sniffing)</p>\r\n</li>\r\n<li class=\"\" data-start=\"1634\" data-end=\"1667\">\r\n<p class=\"\" data-start=\"1636\" data-end=\"1667\">Network protection strategies</p>\r\n</li>\r\n</ul>', '<ul>\r\n<li class=\"\" data-start=\"1691\" data-end=\"1731\">\r\n<p class=\"\" data-start=\"1693\" data-end=\"1731\">Understand basic networking concepts</p>\r\n</li>\r\n<li class=\"\" data-start=\"1732\" data-end=\"1775\">\r\n<p class=\"\" data-start=\"1734\" data-end=\"1775\">Identify common network attack patterns</p>\r\n</li>\r\n<li class=\"\" data-start=\"1776\" data-end=\"1818\">\r\n<p class=\"\" data-start=\"1778\" data-end=\"1818\">Implement basic network security tools</p>\r\n</li>\r\n<li class=\"\" data-start=\"1819\" data-end=\"1857\">\r\n<p class=\"\" data-start=\"1821\" data-end=\"1857\">Set up firewalls and monitor traffic</p>\r\n</li>\r\n</ul>', 'A beginner-friendly guide to understanding how networks operate and how to protect them from attacks.', 120, '2025-05-05 17:20:49', '2025-05-05 23:26:21', 'Approved', 1),
(13, 'Ethical Hacking 101', 'img/1746458565_download (1).jpeg', 'Text-Based', 'intermediate', '<ol>\r\n<li class=\"\" data-start=\"2303\" data-end=\"2338\">\r\n<p class=\"\" data-start=\"2305\" data-end=\"2338\">Introduction to ethical hacking</p>\r\n</li>\r\n<li class=\"\" data-start=\"2339\" data-end=\"2372\">\r\n<p class=\"\" data-start=\"2341\" data-end=\"2372\">Phases of penetration testing</p>\r\n</li>\r\n<li class=\"\" data-start=\"2373\" data-end=\"2408\">\r\n<p class=\"\" data-start=\"2375\" data-end=\"2408\">Common tools (Nmap, Metasploit)</p>\r\n</li>\r\n<li class=\"\" data-start=\"2409\" data-end=\"2445\">\r\n<p class=\"\" data-start=\"2411\" data-end=\"2445\">Legal and ethical consideration</p>\r\n</li>\r\n</ol>', '<ol>\r\n<li class=\"\" data-start=\"2469\" data-end=\"2514\">\r\n<p class=\"\" data-start=\"2471\" data-end=\"2514\">Perform basic reconnaissance and scanning</p>\r\n</li>\r\n<li class=\"\" data-start=\"2515\" data-end=\"2564\">\r\n<p class=\"\" data-start=\"2517\" data-end=\"2564\">Execute simple exploits in a test environment</p>\r\n</li>\r\n<li class=\"\" data-start=\"2565\" data-end=\"2592\">\r\n<p class=\"\" data-start=\"2567\" data-end=\"2592\">Analyze vulnerabilities</p>\r\n</li>\r\n<li class=\"\" data-start=\"2593\" data-end=\"2634\">\r\n<p class=\"\" data-start=\"2595\" data-end=\"2634\">Follow responsible disclosure practices</p>\r\n</li>\r\n</ol>', 'Learn to think like a hacker—ethically. This course teaches the tools and techniques of penetration testing.', 59, '2025-05-05 17:22:45', '2025-05-05 23:26:19', 'Approved', 1),
(14, 'Securing Personal Devices', 'img/1746458666_download (2).jpeg', 'Audio', 'beginner', '<ul>\r\n<li class=\"\" data-start=\"3038\" data-end=\"3078\">\r\n<p class=\"\" data-start=\"3040\" data-end=\"3078\">Mobile and desktop device protection</p>\r\n</li>\r\n<li class=\"\" data-start=\"3079\" data-end=\"3106\">\r\n<p class=\"\" data-start=\"3081\" data-end=\"3106\">App and software safety</p>\r\n</li>\r\n<li class=\"\" data-start=\"3107\" data-end=\"3132\">\r\n<p class=\"\" data-start=\"3109\" data-end=\"3132\">Personal data privacy</p>\r\n</li>\r\n<li class=\"\" data-start=\"3133\" data-end=\"3161\">\r\n<p class=\"\" data-start=\"3135\" data-end=\"3161\">Network security at home</p>\r\n</li>\r\n</ul>', '<ol>\r\n<li class=\"\" data-start=\"3185\" data-end=\"3234\">\r\n<p class=\"\" data-start=\"3187\" data-end=\"3234\">Set up strong password and encryption methods</p>\r\n</li>\r\n<li class=\"\" data-start=\"3235\" data-end=\"3278\">\r\n<p class=\"\" data-start=\"3237\" data-end=\"3278\">Avoid installing risky apps or software</p>\r\n</li>\r\n<li class=\"\" data-start=\"3279\" data-end=\"3312\">\r\n<p class=\"\" data-start=\"3281\" data-end=\"3312\">Recognize suspicious activity</p>\r\n</li>\r\n<li class=\"\" data-start=\"3313\" data-end=\"3347\">\r\n<p class=\"\" data-start=\"3315\" data-end=\"3347\">Enable safe browsing and VPN use</p>\r\n</li>\r\n</ol>', 'Learn how to secure mobile phones, laptops, and tablets from modern threats.', 30, '2025-05-05 17:24:26', '2025-05-05 23:26:17', 'Approved', 1),
(15, ' Web Development Essentials', 'img/1746459135_download.png', 'Visual', 'advanced', '<ul>\r\n<li class=\"\" data-start=\"529\" data-end=\"573\">\r\n<p class=\"\" data-start=\"531\" data-end=\"573\">Learn HTML structure and semantic markup</p>\r\n</li>\r\n<li class=\"\" data-start=\"574\" data-end=\"632\">\r\n<p class=\"\" data-start=\"576\" data-end=\"632\">Style websites using CSS for layout and responsiveness</p>\r\n</li>\r\n<li class=\"\" data-start=\"633\" data-end=\"670\">\r\n<p class=\"\" data-start=\"635\" data-end=\"670\">Add interactivity with JavaScript</p>\r\n</li>\r\n<li class=\"\" data-start=\"671\" data-end=\"726\">\r\n<p class=\"\" data-start=\"673\" data-end=\"726\">Understand principles of web development and design</p>\r\n</li>\r\n<li class=\"\" data-start=\"727\" data-end=\"771\">\r\n<p class=\"\" data-start=\"729\" data-end=\"771\">Explore content interactivity techniques</p>\r\n</li>\r\n<li class=\"\" data-start=\"772\" data-end=\"829\">\r\n<p class=\"\" data-start=\"774\" data-end=\"829\">Learn how to organize and structure software projects</p>\r\n</li>\r\n<li class=\"\" data-start=\"830\" data-end=\"880\">\r\n<p class=\"\" data-start=\"832\" data-end=\"880\">Grasp the basics of system design and planning</p>\r\n</li>\r\n<li class=\"\" data-start=\"881\" data-end=\"928\">\r\n<p class=\"\" data-start=\"883\" data-end=\"928\">Get introduced to PHP for backend scripting</p>\r\n</li>\r\n<li class=\"\" data-start=\"929\" data-end=\"968\">\r\n<p class=\"\" data-start=\"931\" data-end=\"968\">Explore software development cycles</p>\r\n</li>\r\n<li class=\"\" data-start=\"969\" data-end=\"1014\">\r\n<p class=\"\" data-start=\"971\" data-end=\"1014\">Create basic mobile game interfaces for web</p>\r\n</li>\r\n</ul>', '<ul>\r\n<li class=\"\" data-start=\"1032\" data-end=\"1094\">\r\n<p class=\"\" data-start=\"1034\" data-end=\"1094\">Build static and dynamic web pages using HTML, CSS, and JS</p>\r\n</li>\r\n<li class=\"\" data-start=\"1095\" data-end=\"1158\">\r\n<p class=\"\" data-start=\"1097\" data-end=\"1158\">Develop user-friendly and visually appealing web interfaces</p>\r\n</li>\r\n<li class=\"\" data-start=\"1159\" data-end=\"1228\">\r\n<p class=\"\" data-start=\"1161\" data-end=\"1228\">Integrate interactive content like quizzes, forms, and animations</p>\r\n</li>\r\n<li class=\"\" data-start=\"1229\" data-end=\"1296\">\r\n<p class=\"\" data-start=\"1231\" data-end=\"1296\">Understand system analysis and how it applies to frontend logic</p>\r\n</li>\r\n<li class=\"\" data-start=\"1297\" data-end=\"1365\">\r\n<p class=\"\" data-start=\"1299\" data-end=\"1365\">Create small-scale web applications and games using modern tools</p>\r\n</li>\r\n<li class=\"\" data-start=\"1366\" data-end=\"1437\">\r\n<p class=\"\" data-start=\"1368\" data-end=\"1437\">Plan and design structured projects following best software practices</p>\r\n</li>\r\n</ul>', 'This course provides a comprehensive foundation in building websites and user interfaces. Learn to code visually engaging, responsive websites using core web technologies and gain essential skills for creating interactive content and modern frontend applications.', 240, '2025-05-05 17:32:15', '2025-05-05 23:32:15', 'Pending', 1),
(16, 'Backend & Data Management', 'img/1746459234_5130638.png', 'Mixed', 'advanced', '<ul>\r\n<li class=\"\" data-start=\"1762\" data-end=\"1806\">\r\n<p class=\"\" data-start=\"1764\" data-end=\"1806\">Write backend logic with Python and Java</p>\r\n</li>\r\n<li class=\"\" data-start=\"1807\" data-end=\"1870\">\r\n<p class=\"\" data-start=\"1809\" data-end=\"1870\">Manage databases using SQL and understand relational models</p>\r\n</li>\r\n<li class=\"\" data-start=\"1871\" data-end=\"1934\">\r\n<p class=\"\" data-start=\"1873\" data-end=\"1934\">Structure and optimize data using efficient data structures</p>\r\n</li>\r\n<li class=\"\" data-start=\"1935\" data-end=\"1974\">\r\n<p class=\"\" data-start=\"1937\" data-end=\"1974\">Analyze and interpret data patterns</p>\r\n</li>\r\n<li class=\"\" data-start=\"1975\" data-end=\"2015\">\r\n<p class=\"\" data-start=\"1977\" data-end=\"2015\">Learn predictive modeling techniques</p>\r\n</li>\r\n<li class=\"\" data-start=\"2016\" data-end=\"2064\">\r\n<p class=\"\" data-start=\"2018\" data-end=\"2064\">Explore artificial intelligence fundamentals</p>\r\n</li>\r\n<li class=\"\" data-start=\"2065\" data-end=\"2119\">\r\n<p class=\"\" data-start=\"2067\" data-end=\"2119\">Dive into machine learning models and applications</p>\r\n</li>\r\n<li class=\"\" data-start=\"2120\" data-end=\"2171\">\r\n<p class=\"\" data-start=\"2122\" data-end=\"2171\">Study deep learning architectures and use cases</p>\r\n</li>\r\n<li class=\"\" data-start=\"2172\" data-end=\"2227\">\r\n<p class=\"\" data-start=\"2174\" data-end=\"2227\">Understand the flow of backend development and APIs</p>\r\n</li>\r\n<li class=\"\" data-start=\"2228\" data-end=\"2266\">\r\n<p class=\"\" data-start=\"2230\" data-end=\"2266\">Integrate AI/ML into backend systems</p>\r\n</li>\r\n</ul>', '<ul>\r\n<li class=\"\" data-start=\"2284\" data-end=\"2335\">\r\n<p class=\"\" data-start=\"2286\" data-end=\"2335\">Create and manage structured databases with SQL</p>\r\n</li>\r\n<li class=\"\" data-start=\"2336\" data-end=\"2383\">\r\n<p class=\"\" data-start=\"2338\" data-end=\"2383\">Develop backend logic using Python and Java</p>\r\n</li>\r\n<li class=\"\" data-start=\"2384\" data-end=\"2432\">\r\n<p class=\"\" data-start=\"2386\" data-end=\"2432\">Design and analyze efficient data structures</p>\r\n</li>\r\n<li class=\"\" data-start=\"2433\" data-end=\"2495\">\r\n<p class=\"\" data-start=\"2435\" data-end=\"2495\">Apply machine learning and AI to solve real-world problems</p>\r\n</li>\r\n<li class=\"\" data-start=\"2496\" data-end=\"2550\">\r\n<p class=\"\" data-start=\"2498\" data-end=\"2550\">Build data-driven apps using predictive algorithms</p>\r\n</li>\r\n<li class=\"\" data-start=\"2551\" data-end=\"2596\">\r\n<p class=\"\" data-start=\"2553\" data-end=\"2596\">Understand core concepts of deep learning</p>\r\n</li>\r\n<li class=\"\" data-start=\"2597\" data-end=\"2663\">\r\n<p class=\"\" data-start=\"2599\" data-end=\"2663\">Design scalable backend services integrating intelligent systems</p>\r\n</li>\r\n</ul>', 'Master the power behind the web! This course dives deep into server-side programming, databases, and intelligent systems. Perfect for learners who want to build secure, scalable, and data-driven applications with cutting-edge technologies.', 300, '2025-05-05 17:33:54', '2025-05-05 23:33:54', 'Pending', 1);

-- --------------------------------------------------------

--
-- Table structure for table `course_report`
--

CREATE TABLE `course_report` (
  `course_id` int(11) NOT NULL,
  `student_id` int(11) NOT NULL,
  `reason_id` int(11) DEFAULT NULL,
  `reason_desc` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `course_report`
--

INSERT INTO `course_report` (`course_id`, `student_id`, `reason_id`, `reason_desc`) VALUES
(1, 5, 4, 'Weird choice of course image. Doesn\'t match with the course title'),
(1, 6, 6, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `field`
--

CREATE TABLE `field` (
  `field_id` int(11) NOT NULL,
  `name` varchar(50) NOT NULL,
  `description` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `field`
--

INSERT INTO `field` (`field_id`, `name`, `description`) VALUES
(1, 'Hypertext Markup Language (HTML)', NULL),
(2, 'Cascading Style Sheets (CSS)', NULL),
(3, 'JavaScript', NULL),
(4, 'Java', NULL),
(5, 'Web Development', NULL),
(6, 'Structured Query Language (SQL)', NULL),
(7, 'PHP', NULL),
(8, 'Python', NULL),
(9, 'Software Development', NULL),
(10, 'Database Management', NULL),
(11, 'Networking', NULL),
(12, 'Cyber Security', NULL),
(13, 'Digital Forensics', NULL),
(14, 'Data Analysis', NULL),
(15, 'System Analysis', NULL),
(16, 'System Design', NULL),
(17, 'Artificial Intelligence (AI)', NULL),
(18, 'Interactive Content Development', NULL),
(19, 'Deep Learning', NULL),
(20, 'Machine Learning', NULL),
(21, 'Data Structures', NULL),
(22, 'Predictive Modelling', NULL),
(23, 'Mobile Game Development', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `field_preference`
--

CREATE TABLE `field_preference` (
  `student_id` int(11) NOT NULL,
  `field_id` int(11) NOT NULL,
  `experience` varchar(50) NOT NULL,
  `career_occurrence` int(11) NOT NULL DEFAULT 0,
  `activity_score` int(11) NOT NULL DEFAULT 0,
  `recency_score` int(11) NOT NULL DEFAULT 5
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `field_preference`
--

INSERT INTO `field_preference` (`student_id`, `field_id`, `experience`, `career_occurrence`, `activity_score`, `recency_score`) VALUES
(5, 5, 'advanced', 0, 0, 5),
(5, 7, 'intermediate', 0, 0, 5),
(5, 12, 'beginner', 0, 0, 5),
(5, 13, 'beginner', 0, 0, 5),
(5, 17, 'beginner', 0, 0, 5),
(15, 1, 'beginner', 0, 0, 5),
(15, 5, 'intermediate', 0, 0, 5);

-- --------------------------------------------------------

--
-- Table structure for table `forum_post`
--

CREATE TABLE `forum_post` (
  `post_id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `content` text NOT NULL,
  `attachments` varchar(255) NOT NULL,
  `category` varchar(255) NOT NULL,
  `author` varchar(255) NOT NULL,
  `author_email` varchar(100) NOT NULL,
  `post_date` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `forum_post`
--

INSERT INTO `forum_post` (`post_id`, `title`, `content`, `attachments`, `category`, `author`, `author_email`, `post_date`) VALUES
(3, 'maybe', 'maybe', '', 'tutorials', 'Ivan Chin Li Ming', 'lindalim@hotmail.com', '2025-04-22 01:52:46'),
(4, 'asdf', 'wtf', 'uploads/46d931b0c49bcf57cefd93f2d9a016e6.png', 'discussion, feedback', 'Muthu Ramasamy a/l Murugan', 'muthu123@gmail.com', '2025-04-22 11:05:24'),
(5, 'Is Artificial Intelligence taking over the world?', 'provide some artificial intelligence tools that is dominating human race', '', 'tools & resources, tutorials', 'Muthu Ramasamy a/l Murugan', 'muthu123@gmail.com', '2025-04-25 14:26:12'),
(6, 'what is the most interesting topic?', '', '', 'personal stories', 'Muthu Ramasamy a/l Murugan', 'muthu123@gmail.com', '2025-04-26 20:33:44'),
(30, 'yay', 'nae', 'uploads/e2f6f7f56e8312659fe36361da2d72f5.png', 'tools & resources', 'Muthu Ramasamy a/l Murugan', 'muthu123@gmail.com', '2025-04-26 18:17:47'),
(38, 'this good?', 'no?', 'uploads/d2cdd31fee77197a6b2e4bbb360cfe3e.png', 'tutorials', 'Bernard', 'bernard@gmail.com', '2025-05-04 10:56:46');

-- --------------------------------------------------------

--
-- Table structure for table `help_support_qna`
--

CREATE TABLE `help_support_qna` (
  `support_id` int(11) NOT NULL,
  `question` text NOT NULL,
  `answer` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `help_support_qna`
--

INSERT INTO `help_support_qna` (`support_id`, `question`, `answer`) VALUES
(6, 'dsadsa', 'dsadsad'),
(7, 'dsadsa', 'dsadsa'),
(8, 'aaa', 'aaa'),
(9, 'a', 'a');

-- --------------------------------------------------------

--
-- Table structure for table `lecturer`
--

CREATE TABLE `lecturer` (
  `lecturer_id` int(11) NOT NULL,
  `description` text DEFAULT NULL,
  `applicant_id` int(11) NOT NULL,
  `approval_date` datetime NOT NULL DEFAULT current_timestamp(),
  `user_email` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `lecturer`
--

INSERT INTO `lecturer` (`lecturer_id`, `description`, `applicant_id`, `approval_date`, `user_email`) VALUES
(1, 'Lorem ipsum dolor sit amet consectetur adipisicing elit. Earum reprehenderit dolorum enim. Repellendus iusto odit ullam et, a doloremque eaque ducimus aliquam, itaque adipisci maiores dicta molestias ipsum fugiat eos. bkjhkjhk jkhkjhlkjhkj jhkjhkjhlk', 1, '2025-05-02 10:23:22', 'salasiah@gmail.com'),
(2, 'Hello my name is Jon Stewart, you can message me with any questions.', 2, '2025-05-02 10:23:22', 'jonstewart@gmail.com');

-- --------------------------------------------------------

--
-- Table structure for table `lecturer_applicant`
--

CREATE TABLE `lecturer_applicant` (
  `applicant_id` int(11) NOT NULL,
  `teaching_exp` varchar(100) NOT NULL,
  `current_uni_name` varchar(100) NOT NULL,
  `job_title` varchar(100) NOT NULL,
  `application_date` datetime NOT NULL DEFAULT current_timestamp(),
  `application_status` varchar(100) NOT NULL,
  `user_email` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `lecturer_applicant`
--

INSERT INTO `lecturer_applicant` (`applicant_id`, `teaching_exp`, `current_uni_name`, `job_title`, `application_date`, `application_status`, `user_email`) VALUES
(1, 'Less than 1 year', 'BERJAYA University College ', 'Senior Lecturer', '2025-05-01 23:32:36', 'Approved', 'salasiah@gmail.com'),
(2, '10 - 15 years', 'University of Malaya Universiti Malaya (UM) undefined', 'Senior Lecturer', '2025-05-01 23:32:36', 'Approved', 'jonstewart@gmail.com'),
(5, '15 years or more', 'University of Malaya, Universiti Malaya (UM)', 'Senior Professor', '2025-05-01 23:45:49', 'Pending', 'anwar@hotmail.com');

-- --------------------------------------------------------

--
-- Table structure for table `message`
--

CREATE TABLE `message` (
  `message_id` int(11) NOT NULL,
  `text` text NOT NULL,
  `uploads` varchar(255) DEFAULT NULL,
  `deliver_date` datetime NOT NULL,
  `delivery_status` varchar(50) NOT NULL,
  `is_edited` tinyint(1) NOT NULL,
  `edited_date` datetime DEFAULT NULL,
  `receiver_email` varchar(255) NOT NULL,
  `sender_email` varchar(255) NOT NULL,
  `reply_to_message_id` int(11) DEFAULT NULL,
  `replied_content` text DEFAULT NULL,
  `replied_uploads` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `message`
--

INSERT INTO `message` (`message_id`, `text`, `uploads`, `deliver_date`, `delivery_status`, `is_edited`, `edited_date`, `receiver_email`, `sender_email`, `reply_to_message_id`, `replied_content`, `replied_uploads`) VALUES
(91, 'hi', NULL, '2025-05-04 20:05:57', 'read', 0, NULL, 'muthu123@gmail.com', 'bernard@gmail.com', NULL, NULL, NULL),
(92, 'u suck', NULL, '2025-05-04 20:06:07', 'read', 0, NULL, 'muthu123@gmail.com', 'bernard@gmail.com', NULL, NULL, NULL),
(93, 'hi', NULL, '2025-05-04 20:25:18', 'sent', 0, NULL, 'muthu123@gmail.com', 'bernard@gmail.com', NULL, NULL, NULL),
(94, 'I know', NULL, '2025-05-04 21:08:45', 'read', 0, NULL, 'bernard@gmail.com', 'muthu123@gmail.com', NULL, 'here', NULL),
(95, 'this', NULL, '2025-05-04 21:09:01', 'read', 0, NULL, 'bernard@gmail.com', 'muthu123@gmail.com', NULL, '', NULL),
(96, 'yay', NULL, '2025-05-04 21:10:50', 'read', 0, NULL, 'bernard@gmail.com', 'muthu123@gmail.com', NULL, 'here', NULL),
(97, 'try again', NULL, '2025-05-04 21:17:38', 'read', 0, NULL, 'bernard@gmail.com', 'muthu123@gmail.com', NULL, 'cry???', NULL),
(98, 'again', NULL, '2025-05-04 21:40:32', 'read', 0, NULL, 'bernard@gmail.com', 'muthu123@gmail.com', NULL, 'here', NULL),
(99, 'do', NULL, '2025-05-04 21:45:12', 'sent', 0, NULL, 'muthu123@gmail.com', 'bernard@gmail.com', NULL, '', 'uploads/f5268c1b3b1e87a87769e0597affc0bc.png'),
(100, 'ha', NULL, '2025-05-04 21:48:16', 'sent', 0, NULL, 'muthu123@gmail.com', 'bernard@gmail.com', NULL, '', 'uploads/f5268c1b3b1e87a87769e0597affc0bc.png'),
(101, 'yo', NULL, '2025-05-04 21:49:36', 'sent', 0, NULL, 'muthu123@gmail.com', 'bernard@gmail.com', NULL, 'yes', 'Uploads/3a2090c3f92d59a502aab8a8419934ee.png'),
(102, 'hi', NULL, '2025-05-04 22:30:47', 'sent', 0, NULL, 'muthu123@gmail.com', 'bernard@gmail.com', NULL, '', 'uploads/f5268c1b3b1e87a87769e0597affc0bc.png');

-- --------------------------------------------------------

--
-- Table structure for table `people`
--

CREATE TABLE `people` (
  `user_id` int(11) NOT NULL,
  `unique_id` int(255) NOT NULL,
  `user_name` varchar(255) NOT NULL,
  `user_email` varchar(255) NOT NULL,
  `user_password` varchar(255) NOT NULL,
  `user_role` varchar(255) NOT NULL,
  `image` varchar(255) NOT NULL,
  `status` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `people`
--

INSERT INTO `people` (`user_id`, `unique_id`, `user_name`, `user_email`, `user_password`, `user_role`, `image`, `status`) VALUES
(1, 123456, 'Pooh', 'itssurely@gmail.com', 'blablabla', 'student', 'a4a0b3378248d55542a4c8f7008180fc.jpg', 'online'),
(2, 654321, 'Gojo', 'sexyman@gmail.com', 'lalalala', 'lecturer', '2e0f0f479d54e96b0a249b618b9d2a0f.jpg', 'online'),
(5, 345678, 'Get Help', 'gethelp01@gmail.com', 'qwertywasd', 'admin', 'blank-profile-picture-973460_960_720.jpg', 'online'),
(6, 444666, 'Support guy', 'motherf@gmail.com', 'noobmaster69', 'admin', 'c6dc27ad89c8844a1a5deafde2b03a80.jpg', 'online');

-- --------------------------------------------------------

--
-- Table structure for table `post_votes`
--

CREATE TABLE `post_votes` (
  `vote_id` int(11) NOT NULL,
  `post_id` int(11) NOT NULL,
  `author_email` varchar(255) NOT NULL,
  `is_up_vote` tinyint(1) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `post_votes`
--

INSERT INTO `post_votes` (`vote_id`, `post_id`, `author_email`, `is_up_vote`) VALUES
(63, 5, 'bernard@gmail.com', 1),
(64, 3, 'ivan@gmail.com', 1);

-- --------------------------------------------------------

--
-- Table structure for table `progression`
--

CREATE TABLE `progression` (
  `enrol_id` int(11) NOT NULL,
  `section_id` int(11) NOT NULL,
  `progress` tinyint(1) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `progression`
--

INSERT INTO `progression` (`enrol_id`, `section_id`, `progress`) VALUES
(19, 12, 1);

-- --------------------------------------------------------

--
-- Table structure for table `question`
--

CREATE TABLE `question` (
  `question_id` int(11) NOT NULL,
  `question_text` text NOT NULL,
  `media_url` text NOT NULL,
  `question_type` varchar(50) NOT NULL,
  `section_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `question`
--

INSERT INTO `question` (`question_id`, `question_text`, `media_url`, `question_type`, `section_id`) VALUES
(6, 'What is the main goal of cybersecurity?', '', 'mcq', 17),
(7, 'Which of the following is a type of malware?', '', 'mcq', 17),
(8, 'Phishing attacks often use what to trick users?', '', 'mcq', 17),
(9, 'Which is an example of multi-factor authentication?', '', 'mcq', 19),
(10, 'You should avoid clicking on links in:', '', 'mcq', 19),
(11, 'Which of the following helps protect your online accounts?', '', 'mcq', 19),
(12, 'What does a firewall do?', '', 'mcq', 22),
(13, 'Which of these was a major ransomware attack in 2017?', '', 'mcq', 22),
(14, 'Antivirus software is used to:', '', 'mcq', 22),
(16, 'What is the difference between a shallow copy and a deep copy in Python? Give a simple code example.', '', 'saq', 29),
(17, 'Look at the image below. What will be the output of this code snippet? Explain why.', 'lec_uploads/1746459615_1_WB7bHNq.jpeg', 'saq', 29);

-- --------------------------------------------------------

--
-- Table structure for table `quiz_performance`
--

CREATE TABLE `quiz_performance` (
  `quiz_perform_id` int(11) NOT NULL,
  `percentage` int(11) NOT NULL,
  `enrol_id` int(11) NOT NULL,
  `section_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `removed_user`
--

CREATE TABLE `removed_user` (
  `user_email` varchar(100) NOT NULL,
  `name` varchar(100) NOT NULL,
  `removed_reason` text NOT NULL,
  `removed_status` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `removed_user`
--

INSERT INTO `removed_user` (`user_email`, `name`, `removed_reason`, `removed_status`) VALUES
('hueyling@gmail.com', 'Huey Ling', 'We have rejected you lecturer application due to incomplete information. Thank you for your interest. ', 'Removed'),
('joshua@gmail.com', 'Joshua', 'Inappropriate behaviour in forum', 'Banned');

-- --------------------------------------------------------

--
-- Table structure for table `replies`
--

CREATE TABLE `replies` (
  `reply_id` int(11) NOT NULL,
  `post_id` int(11) NOT NULL,
  `author_email` varchar(255) NOT NULL,
  `replied_by` varchar(255) NOT NULL,
  `reply_content` text NOT NULL,
  `reply_attachment` varchar(255) NOT NULL,
  `posted_date` datetime NOT NULL,
  `edited_date` datetime NOT NULL,
  `is_edited` tinyint(1) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `replies`
--

INSERT INTO `replies` (`reply_id`, `post_id`, `author_email`, `replied_by`, `reply_content`, `reply_attachment`, `posted_date`, `edited_date`, `is_edited`) VALUES
(9, 4, 'bernard@gmail.com', 'Bernard', 'this is wrong', '', '2025-05-01 11:53:05', '2025-05-01 11:53:05', 0),
(12, 38, 'bernard@gmail.com', 'Bernard', 'I finished', 'uploads/reply_68172bec855037.03669064.png', '2025-05-04 16:57:16', '2025-05-04 16:57:16', 0),
(13, 3, 'bernard@gmail.com', 'Bernard', 'asa', 'uploads/reply_681747cea78114.50551842.gif', '2025-05-04 18:56:14', '2025-05-04 18:56:14', 0),
(14, 38, 'bernard@gmail.com', 'Bernard', 'this better?', 'uploads/reply_68174a4dca7cc1.11120775.gif', '2025-05-04 19:06:53', '2025-05-05 01:33:26', 1),
(16, 3, 'bernard@gmail.com', 'Bernard', 'this one', 'uploads/reply_6817a5d024c4a1.22336806.gif', '2025-05-05 01:37:20', '2025-05-05 01:37:20', 0);

-- --------------------------------------------------------

--
-- Table structure for table `reply_reports`
--

CREATE TABLE `reply_reports` (
  `report_id` int(11) NOT NULL,
  `reply_id` int(11) NOT NULL,
  `reporter_id` varchar(100) NOT NULL,
  `report_categories` varchar(255) NOT NULL,
  `reason` text DEFAULT NULL,
  `report_date` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `reply_reports`
--

INSERT INTO `reply_reports` (`report_id`, `reply_id`, `reporter_id`, `report_categories`, `reason`, `report_date`) VALUES
(7, 12, 'muthu123@gmail.com', 'Inappropriate, Vulgar Language', 'boohoo', '2025-05-05 01:31:15');

-- --------------------------------------------------------

--
-- Table structure for table `reports`
--

CREATE TABLE `reports` (
  `report_id` int(11) NOT NULL,
  `user_email` varchar(255) NOT NULL,
  `post_id` int(11) NOT NULL,
  `report_category` varchar(255) NOT NULL,
  `reason_desc` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `reports`
--

INSERT INTO `reports` (`report_id`, `user_email`, `post_id`, `report_category`, `reason_desc`) VALUES
(9, 'muthu123@gmail.com', 3, 'other', 'nop'),
(11, 'muthu123@gmail.com', 5, 'off_topic', 'off');

-- --------------------------------------------------------

--
-- Table structure for table `report_reason`
--

CREATE TABLE `report_reason` (
  `reason_id` int(11) NOT NULL,
  `report_type` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `report_reason`
--

INSERT INTO `report_reason` (`reason_id`, `report_type`) VALUES
(1, 'spam'),
(2, 'harrassment'),
(3, 'hate speech'),
(4, 'off-topic'),
(5, 'political'),
(6, 'misinformation'),
(7, 'inappropriate'),
(8, 'vulgar language'),
(9, 'other');

-- --------------------------------------------------------

--
-- Table structure for table `review_report`
--

CREATE TABLE `review_report` (
  `student_id` int(11) NOT NULL,
  `enrol_id` int(11) NOT NULL,
  `reason_id` int(11) NOT NULL,
  `reason_desc` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `saved_course`
--

CREATE TABLE `saved_course` (
  `student_id` int(11) NOT NULL,
  `course_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `saved_course`
--

INSERT INTO `saved_course` (`student_id`, `course_id`) VALUES
(2, 1),
(3, 1),
(5, 1),
(5, 4),
(5, 5),
(5, 6),
(5, 7),
(5, 8);

-- --------------------------------------------------------

--
-- Table structure for table `section`
--

CREATE TABLE `section` (
  `section_id` int(11) NOT NULL,
  `section_title` varchar(100) NOT NULL,
  `subtopic_description` text NOT NULL,
  `type` varchar(50) NOT NULL,
  `chapter_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `section`
--

INSERT INTO `section` (`section_id`, `section_title`, `subtopic_description`, `type`, `chapter_id`) VALUES
(5, 'Introduction of AI', 'At the end of this topic, you should be able to: define artificial intelligence \r\n,define human intelligence, relate AI problems to their origins, explain the criteria for AI success\r\n', 'subtopic', 3),
(6, 'Introduction to AI - Review Quiz', 'Test your understanding of the fundamentals, background and definitions surrounding AI. You are not encouraged to move on to the following chapters if you did not score 80% and above. ', 'quiz', 3),
(7, 'Introduction to Expert System', '', 'subtopic', 4),
(8, 'Expert Systems - Review Quiz', '', 'quiz', 4),
(9, 'Real Life Implementation of Expert System', '', 'subtopic', 4),
(10, 'AI Chap 2 Knowledge Quiz', '', 'quiz', 4),
(12, 'Client-Server Architecture', 'Explains the architecture in detail.', 'content', 6),
(14, 'Subtopic 1.1: What is Cybersecurity?', 'Understand the definition, scope, and importance of cybersecurity in todays digital world.', 'content', 7),
(15, 'Subtopic 2.1: Strong Passwords and Authentication', 'Discover the role of strong passwords and multi-factor authentication.', 'content', 8),
(16, 'Subtopic 1.2: Types of Cyber Threats', ' Learn about malware, phishing, ransomware, and other common threats.', 'content', 7),
(17, '❓ Quiz 1: Cybersecurity Basics', 'Test your Cybersecurity Basics level', 'question', 7),
(18, 'Subtopic 2.2: Safe Browsing and Email Habits', 'Learn how to stay safe online and avoid scams.', 'content', 8),
(19, '❓ Quiz 2: Personal Security Practices', ' ', 'question', 8),
(20, 'Subtopic 3.1: Firewalls and Antivirus Software', 'Introduction to basic security tools used to defend against threats.', 'content', 9),
(21, 'Subtopic 3.2: Real-World Cybersecurity Incidents', 'Case studies on major cyberattacks like the Equifax breach and WannaCry.', 'content', 9),
(22, '❓ Quiz 3: Tools and Incidents', 'Final Test', 'question', 9),
(23, 'Classes and Objects', 'Learn how to define classes, instantiate objects, and use constructors.', 'content', 10),
(24, 'Decorators and Generators', 'Explore the use of @decorators and efficient looping with generators.', 'content', 11),
(25, 'API Integration using requests', 'Fetch data from web APIs and handle JSON responses.', 'content', 12),
(26, 'Inheritance and Polymorphism', 'Understand how child classes can inherit properties and override methods.', 'content', 10),
(27, 'Data Serialization with pickle and json', 'Save and load complex data structures in Python formats.', 'content', 12),
(28, 'Context Managers and the with Statement', 'Handle resources like files and DB connections more safely and efficiently.', 'content', 11),
(29, 'Quiz: Python Knowledge Check', 'FINAL TEST before done', 'question', 12);

-- --------------------------------------------------------

--
-- Table structure for table `student`
--

CREATE TABLE `student` (
  `student_id` int(11) NOT NULL,
  `edu_level` varchar(50) NOT NULL,
  `learning_style` varchar(50) NOT NULL,
  `recent_course_list` varchar(500) DEFAULT NULL,
  `sign_up_date` date NOT NULL DEFAULT current_timestamp(),
  `user_email` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `student`
--

INSERT INTO `student` (`student_id`, `edu_level`, `learning_style`, `recent_course_list`, `sign_up_date`, `user_email`) VALUES
(2, 'High School', 'Audio', ',3,1', '2025-05-03', 'bernard@gmail.com'),
(3, 'Master\'s Degree', 'Mixed', ',1', '2025-05-03', 'muthu123@gmail.com'),
(5, 'Bachelor\'s Degree', 'Text-Based', '3,6,10,4,2,5,7,1', '2024-12-04', 'lindalim@hotmail.com'),
(6, 'Diploma', 'Visual', NULL, '2025-05-03', 'wenyew@gmail.com'),
(9, 'Diploma', 'Audio', NULL, '2025-05-04', 'col@gmail.com'),
(15, 'Bachelor\'s Degree', 'Audio', NULL, '2025-05-04', 'hello@gmail.com');

-- --------------------------------------------------------

--
-- Table structure for table `user`
--

CREATE TABLE `user` (
  `user_email` varchar(100) NOT NULL,
  `password` varchar(128) NOT NULL,
  `name` varchar(100) NOT NULL,
  `role` varchar(100) NOT NULL,
  `pfp` varchar(100) DEFAULT 'profile/defaultProfile.jpg',
  `DOB` date NOT NULL,
  `theme` varchar(30) NOT NULL DEFAULT 'light',
  `forum_karma` int(11) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `user`
--

INSERT INTO `user` (`user_email`, `password`, `name`, `role`, `pfp`, `DOB`, `theme`, `forum_karma`) VALUES
('adam@gmail.com', '$2y$10$xU97pLTixP4Bo9hQr9O0AuFnJUQ/hpJ60J1FbZ67cMmxBI5DFp/RO', 'Adam', 'admin', 'profile/defaultProfile.jpg', '1990-05-04', 'light', 0),
('anwar@hotmail.com', '$2y$10$4LjELW4LHoAIsWHbbRycpeh1GysjnYOFfgRrxZvNmUxZiSU5nYJfe', 'Anwar Ibrahim', 'pending lecturer', 'profile/defaultProfile.jpg', '1948-02-14', 'light', 0),
('bernard@gmail.com', '$2y$10$g5oXXKEDXS8ayzCrZlkMBOG2Pk0xAmPFLH7EtwNSf7pCDO4nmvkS6', 'Bernard', 'student', 'profile/defaultProfile.jpg', '2023-10-25', 'light', 3),
('col@gmail.com', '$2y$10$DlqJ3rZXD/siSpj0A2AET.5KwKFjEWQ3H6mF8uqtZl90gO1Xy8gyO', 'Colwyn', 'student', 'profile/defaultProfile.jpg', '2025-04-12', 'light', 0),
('hello@gmail.com', '$2y$10$MAhSEgqe.mS80Rz38PqDHeMDDjca/.peXr6g2zjj90nJLOGfQzGHa', 'Hello', 'student', 'profile/defaultProfile.jpg', '2025-04-17', 'light', 0),
('ivan@gmail.com', '$2y$10$G4r3.VGUM98g2dkJtLMslesuefBtBca3t8Tt9ZOGFyt2YZbVdLANe', 'Ivan Chin', 'admin', 'profile/defaultProfile.jpg', '2005-02-14', 'light', 0),
('jonstewart@gmail.com', '$2y$10$pacyXoeeWvY6I55Qfbr4uuANqpuHWllylVmr6sBsXuAua/lqm.rhy', 'Jon Stewart', 'lecturer', 'profile/defaultProfile.jpg', '1960-04-30', 'dark', 10),
('lindalim@hotmail.com', '$2y$10$rx8GM5.XBJ9r3QH.j2Y66eWHhwMvIIsOrJYWUhbaVDruhQlFvH3Ay', 'Ivan Chin Li Ming', 'student', 'profile/lindalim_at_hotmail_dot_com.jpg', '2004-01-01', 'light', 0),
('muthu123@gmail.com', '$2y$10$DgKnhvMFRYJmKiRD8y/nd.t9/ATwEqOkjtpaIoYrIXyTgmKfFCEqq', 'Muthu Ramasamy a/l Murugan', 'student', 'profile/muthu123_at_gmail_dot_com.jpg', '1995-12-02', 'light', 0),
('salasiah@gmail.com', '$2y$10$ApM8KeIVFNLcttIqWtn.X.f7Xc0IyIZuvXgiuAxrw5Nw2xGGjvTp6', 'Salasiah Sulaiman', 'lecturer', 'profile/defaultProfile.jpg', '1985-03-01', 'light', 0),
('wenyew@gmail.com', '$2y$10$aNyW0QSMZ88eow69Pp0G6eZMxlA1s/Sh0p1F8YjnJR/Nw7m6ym61G', 'Wen Yew', 'student', 'profile/wenyew_at_gmail_dot_com.jpg', '2005-04-26', 'light', 5);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `admin`
--
ALTER TABLE `admin`
  ADD PRIMARY KEY (`admin_id`),
  ADD KEY `user_email` (`user_email`);

--
-- Indexes for table `answer`
--
ALTER TABLE `answer`
  ADD PRIMARY KEY (`answer_id`),
  ADD KEY `question_id` (`question_id`);

--
-- Indexes for table `applicant_document`
--
ALTER TABLE `applicant_document`
  ADD PRIMARY KEY (`document_id`),
  ADD KEY `applicant_id` (`applicant_id`);

--
-- Indexes for table `career`
--
ALTER TABLE `career`
  ADD PRIMARY KEY (`career_id`);

--
-- Indexes for table `career_field_relation`
--
ALTER TABLE `career_field_relation`
  ADD PRIMARY KEY (`career_id`,`field_id`),
  ADD KEY `field_id` (`field_id`);

--
-- Indexes for table `career_preference`
--
ALTER TABLE `career_preference`
  ADD PRIMARY KEY (`student_id`,`career_id`),
  ADD KEY `career_id` (`career_id`);

--
-- Indexes for table `chapter`
--
ALTER TABLE `chapter`
  ADD PRIMARY KEY (`chapter_id`),
  ADD KEY `course_id` (`course_id`);

--
-- Indexes for table `content`
--
ALTER TABLE `content`
  ADD PRIMARY KEY (`content_id`),
  ADD KEY `section_id` (`section_id`);

--
-- Indexes for table `course`
--
ALTER TABLE `course`
  ADD PRIMARY KEY (`course_id`),
  ADD UNIQUE KEY `proposal_id` (`proposal_id`),
  ADD KEY `lecturer_id` (`lecturer_id`);

--
-- Indexes for table `course_enrolment`
--
ALTER TABLE `course_enrolment`
  ADD PRIMARY KEY (`enrol_id`),
  ADD KEY `course_enrolment_ibfk_1` (`course_id`),
  ADD KEY `student_id` (`student_id`);

--
-- Indexes for table `course_field`
--
ALTER TABLE `course_field`
  ADD PRIMARY KEY (`proposal_id`,`field_id`),
  ADD KEY `field_id` (`field_id`);

--
-- Indexes for table `course_proposal`
--
ALTER TABLE `course_proposal`
  ADD PRIMARY KEY (`proposal_id`),
  ADD KEY `lecturer_id` (`lecturer_id`);

--
-- Indexes for table `course_report`
--
ALTER TABLE `course_report`
  ADD PRIMARY KEY (`course_id`,`student_id`),
  ADD KEY `course_report_ibfk_1` (`reason_id`),
  ADD KEY `student_id` (`student_id`);

--
-- Indexes for table `field`
--
ALTER TABLE `field`
  ADD PRIMARY KEY (`field_id`);

--
-- Indexes for table `field_preference`
--
ALTER TABLE `field_preference`
  ADD PRIMARY KEY (`student_id`,`field_id`),
  ADD KEY `field_id` (`field_id`);

--
-- Indexes for table `forum_post`
--
ALTER TABLE `forum_post`
  ADD PRIMARY KEY (`post_id`),
  ADD KEY `author_email` (`author_email`);

--
-- Indexes for table `help_support_qna`
--
ALTER TABLE `help_support_qna`
  ADD PRIMARY KEY (`support_id`);

--
-- Indexes for table `lecturer`
--
ALTER TABLE `lecturer`
  ADD PRIMARY KEY (`lecturer_id`),
  ADD UNIQUE KEY `applicant_id` (`applicant_id`),
  ADD UNIQUE KEY `user_email` (`user_email`);

--
-- Indexes for table `lecturer_applicant`
--
ALTER TABLE `lecturer_applicant`
  ADD PRIMARY KEY (`applicant_id`),
  ADD UNIQUE KEY `user_email` (`user_email`);

--
-- Indexes for table `message`
--
ALTER TABLE `message`
  ADD PRIMARY KEY (`message_id`),
  ADD KEY `receiver_email` (`receiver_email`),
  ADD KEY `sender_email` (`sender_email`),
  ADD KEY `reply_to_message_id` (`reply_to_message_id`);

--
-- Indexes for table `people`
--
ALTER TABLE `people`
  ADD PRIMARY KEY (`user_id`),
  ADD UNIQUE KEY `unique_id` (`unique_id`,`user_email`),
  ADD KEY `idx_user_email` (`user_email`);

--
-- Indexes for table `post_votes`
--
ALTER TABLE `post_votes`
  ADD PRIMARY KEY (`vote_id`),
  ADD UNIQUE KEY `post_id` (`post_id`,`author_email`),
  ADD KEY `author_email` (`author_email`);

--
-- Indexes for table `progression`
--
ALTER TABLE `progression`
  ADD PRIMARY KEY (`enrol_id`,`section_id`),
  ADD KEY `section_id` (`section_id`);

--
-- Indexes for table `question`
--
ALTER TABLE `question`
  ADD PRIMARY KEY (`question_id`),
  ADD KEY `section_id` (`section_id`);

--
-- Indexes for table `quiz_performance`
--
ALTER TABLE `quiz_performance`
  ADD PRIMARY KEY (`quiz_perform_id`),
  ADD KEY `enrol_id` (`enrol_id`),
  ADD KEY `section_id` (`section_id`);

--
-- Indexes for table `removed_user`
--
ALTER TABLE `removed_user`
  ADD PRIMARY KEY (`user_email`);

--
-- Indexes for table `replies`
--
ALTER TABLE `replies`
  ADD PRIMARY KEY (`reply_id`),
  ADD KEY `author_email` (`author_email`),
  ADD KEY `post_id` (`post_id`);

--
-- Indexes for table `reply_reports`
--
ALTER TABLE `reply_reports`
  ADD PRIMARY KEY (`report_id`),
  ADD KEY `reply_id` (`reply_id`),
  ADD KEY `reply_reports_ibfk_2` (`reporter_id`);

--
-- Indexes for table `reports`
--
ALTER TABLE `reports`
  ADD PRIMARY KEY (`report_id`),
  ADD KEY `post_id` (`post_id`),
  ADD KEY `user_email` (`user_email`);

--
-- Indexes for table `report_reason`
--
ALTER TABLE `report_reason`
  ADD PRIMARY KEY (`reason_id`);

--
-- Indexes for table `review_report`
--
ALTER TABLE `review_report`
  ADD PRIMARY KEY (`student_id`,`enrol_id`),
  ADD KEY `enrol_id` (`enrol_id`),
  ADD KEY `reason_id` (`reason_id`);

--
-- Indexes for table `saved_course`
--
ALTER TABLE `saved_course`
  ADD PRIMARY KEY (`student_id`,`course_id`),
  ADD KEY `course_id` (`course_id`);

--
-- Indexes for table `section`
--
ALTER TABLE `section`
  ADD PRIMARY KEY (`section_id`),
  ADD KEY `chapter_id` (`chapter_id`);

--
-- Indexes for table `student`
--
ALTER TABLE `student`
  ADD PRIMARY KEY (`student_id`),
  ADD UNIQUE KEY `user_email` (`user_email`);

--
-- Indexes for table `user`
--
ALTER TABLE `user`
  ADD PRIMARY KEY (`user_email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `admin`
--
ALTER TABLE `admin`
  MODIFY `admin_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `answer`
--
ALTER TABLE `answer`
  MODIFY `answer_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=54;

--
-- AUTO_INCREMENT for table `applicant_document`
--
ALTER TABLE `applicant_document`
  MODIFY `document_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `career`
--
ALTER TABLE `career`
  MODIFY `career_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `chapter`
--
ALTER TABLE `chapter`
  MODIFY `chapter_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `content`
--
ALTER TABLE `content`
  MODIFY `content_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT for table `course`
--
ALTER TABLE `course`
  MODIFY `course_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT for table `course_enrolment`
--
ALTER TABLE `course_enrolment`
  MODIFY `enrol_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=20;

--
-- AUTO_INCREMENT for table `course_proposal`
--
ALTER TABLE `course_proposal`
  MODIFY `proposal_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT for table `field`
--
ALTER TABLE `field`
  MODIFY `field_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=24;

--
-- AUTO_INCREMENT for table `forum_post`
--
ALTER TABLE `forum_post`
  MODIFY `post_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=39;

--
-- AUTO_INCREMENT for table `help_support_qna`
--
ALTER TABLE `help_support_qna`
  MODIFY `support_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `lecturer`
--
ALTER TABLE `lecturer`
  MODIFY `lecturer_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `lecturer_applicant`
--
ALTER TABLE `lecturer_applicant`
  MODIFY `applicant_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT for table `message`
--
ALTER TABLE `message`
  MODIFY `message_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=103;

--
-- AUTO_INCREMENT for table `people`
--
ALTER TABLE `people`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `post_votes`
--
ALTER TABLE `post_votes`
  MODIFY `vote_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=65;

--
-- AUTO_INCREMENT for table `question`
--
ALTER TABLE `question`
  MODIFY `question_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- AUTO_INCREMENT for table `quiz_performance`
--
ALTER TABLE `quiz_performance`
  MODIFY `quiz_perform_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `replies`
--
ALTER TABLE `replies`
  MODIFY `reply_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT for table `reply_reports`
--
ALTER TABLE `reply_reports`
  MODIFY `report_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `reports`
--
ALTER TABLE `reports`
  MODIFY `report_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `report_reason`
--
ALTER TABLE `report_reason`
  MODIFY `reason_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=41;

--
-- AUTO_INCREMENT for table `section`
--
ALTER TABLE `section`
  MODIFY `section_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=30;

--
-- AUTO_INCREMENT for table `student`
--
ALTER TABLE `student`
  MODIFY `student_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `admin`
--
ALTER TABLE `admin`
  ADD CONSTRAINT `admin_ibfk_1` FOREIGN KEY (`user_email`) REFERENCES `user` (`user_email`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `answer`
--
ALTER TABLE `answer`
  ADD CONSTRAINT `answer_ibfk_1` FOREIGN KEY (`question_id`) REFERENCES `question` (`question_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `applicant_document`
--
ALTER TABLE `applicant_document`
  ADD CONSTRAINT `applicant_document_ibfk_1` FOREIGN KEY (`applicant_id`) REFERENCES `lecturer_applicant` (`applicant_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `career_field_relation`
--
ALTER TABLE `career_field_relation`
  ADD CONSTRAINT `career_field_relation_ibfk_1` FOREIGN KEY (`career_id`) REFERENCES `career` (`career_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `career_field_relation_ibfk_2` FOREIGN KEY (`field_id`) REFERENCES `field` (`field_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `career_preference`
--
ALTER TABLE `career_preference`
  ADD CONSTRAINT `career_preference_ibfk_1` FOREIGN KEY (`student_id`) REFERENCES `student` (`student_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `career_preference_ibfk_2` FOREIGN KEY (`career_id`) REFERENCES `career` (`career_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `chapter`
--
ALTER TABLE `chapter`
  ADD CONSTRAINT `chapter_ibfk_1` FOREIGN KEY (`course_id`) REFERENCES `course` (`course_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `course`
--
ALTER TABLE `course`
  ADD CONSTRAINT `course_ibfk_1` FOREIGN KEY (`lecturer_id`) REFERENCES `lecturer` (`lecturer_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `course_ibfk_2` FOREIGN KEY (`proposal_id`) REFERENCES `course_proposal` (`proposal_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `course_enrolment`
--
ALTER TABLE `course_enrolment`
  ADD CONSTRAINT `course_enrolment_ibfk_1` FOREIGN KEY (`course_id`) REFERENCES `course` (`course_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `course_enrolment_ibfk_2` FOREIGN KEY (`student_id`) REFERENCES `student` (`student_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `course_field`
--
ALTER TABLE `course_field`
  ADD CONSTRAINT `course_field_ibfk_1` FOREIGN KEY (`proposal_id`) REFERENCES `course_proposal` (`proposal_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `course_field_ibfk_2` FOREIGN KEY (`field_id`) REFERENCES `field` (`field_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `course_proposal`
--
ALTER TABLE `course_proposal`
  ADD CONSTRAINT `course_proposal_ibfk_1` FOREIGN KEY (`lecturer_id`) REFERENCES `lecturer` (`lecturer_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `course_report`
--
ALTER TABLE `course_report`
  ADD CONSTRAINT `course_report_ibfk_1` FOREIGN KEY (`course_id`) REFERENCES `course` (`course_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `course_report_ibfk_2` FOREIGN KEY (`student_id`) REFERENCES `student` (`student_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `course_report_ibfk_3` FOREIGN KEY (`reason_id`) REFERENCES `report_reason` (`reason_id`);

--
-- Constraints for table `field_preference`
--
ALTER TABLE `field_preference`
  ADD CONSTRAINT `field_preference_ibfk_1` FOREIGN KEY (`student_id`) REFERENCES `student` (`student_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `field_preference_ibfk_2` FOREIGN KEY (`field_id`) REFERENCES `field` (`field_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `forum_post`
--
ALTER TABLE `forum_post`
  ADD CONSTRAINT `forum_post_ibfk_1` FOREIGN KEY (`author_email`) REFERENCES `user` (`user_email`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `lecturer`
--
ALTER TABLE `lecturer`
  ADD CONSTRAINT `lecturer_ibfk_1` FOREIGN KEY (`applicant_id`) REFERENCES `lecturer_applicant` (`applicant_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `lecturer_ibfk_2` FOREIGN KEY (`user_email`) REFERENCES `user` (`user_email`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `lecturer_applicant`
--
ALTER TABLE `lecturer_applicant`
  ADD CONSTRAINT `lecturer_applicant_ibfk_1` FOREIGN KEY (`user_email`) REFERENCES `user` (`user_email`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `message`
--
ALTER TABLE `message`
  ADD CONSTRAINT `message_ibfk_1` FOREIGN KEY (`reply_to_message_id`) REFERENCES `message` (`message_id`) ON DELETE SET NULL,
  ADD CONSTRAINT `message_ibfk_2` FOREIGN KEY (`receiver_email`) REFERENCES `user` (`user_email`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `message_ibfk_3` FOREIGN KEY (`sender_email`) REFERENCES `user` (`user_email`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `post_votes`
--
ALTER TABLE `post_votes`
  ADD CONSTRAINT `post_votes_ibfk_1` FOREIGN KEY (`author_email`) REFERENCES `user` (`user_email`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `post_votes_ibfk_2` FOREIGN KEY (`post_id`) REFERENCES `forum_post` (`post_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `progression`
--
ALTER TABLE `progression`
  ADD CONSTRAINT `progression_ibfk_1` FOREIGN KEY (`enrol_id`) REFERENCES `course_enrolment` (`enrol_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `progression_ibfk_2` FOREIGN KEY (`section_id`) REFERENCES `section` (`section_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `question`
--
ALTER TABLE `question`
  ADD CONSTRAINT `question_ibfk_1` FOREIGN KEY (`section_id`) REFERENCES `section` (`section_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `quiz_performance`
--
ALTER TABLE `quiz_performance`
  ADD CONSTRAINT `quiz_performance_ibfk_1` FOREIGN KEY (`enrol_id`) REFERENCES `course_enrolment` (`enrol_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `quiz_performance_ibfk_2` FOREIGN KEY (`section_id`) REFERENCES `section` (`section_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `replies`
--
ALTER TABLE `replies`
  ADD CONSTRAINT `replies_ibfk_1` FOREIGN KEY (`author_email`) REFERENCES `user` (`user_email`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `replies_ibfk_2` FOREIGN KEY (`post_id`) REFERENCES `forum_post` (`post_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `reply_reports`
--
ALTER TABLE `reply_reports`
  ADD CONSTRAINT `reply_reports_ibfk_1` FOREIGN KEY (`reply_id`) REFERENCES `replies` (`reply_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `reply_reports_ibfk_2` FOREIGN KEY (`reporter_id`) REFERENCES `user` (`user_email`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `reports`
--
ALTER TABLE `reports`
  ADD CONSTRAINT `reports_ibfk_1` FOREIGN KEY (`post_id`) REFERENCES `forum_post` (`post_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `reports_ibfk_2` FOREIGN KEY (`user_email`) REFERENCES `user` (`user_email`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `review_report`
--
ALTER TABLE `review_report`
  ADD CONSTRAINT `review_report_ibfk_1` FOREIGN KEY (`enrol_id`) REFERENCES `course_enrolment` (`enrol_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `review_report_ibfk_2` FOREIGN KEY (`reason_id`) REFERENCES `report_reason` (`reason_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `review_report_ibfk_3` FOREIGN KEY (`student_id`) REFERENCES `student` (`student_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `saved_course`
--
ALTER TABLE `saved_course`
  ADD CONSTRAINT `saved_course_ibfk_1` FOREIGN KEY (`student_id`) REFERENCES `student` (`student_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `saved_course_ibfk_2` FOREIGN KEY (`course_id`) REFERENCES `course` (`course_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `section`
--
ALTER TABLE `section`
  ADD CONSTRAINT `section_ibfk_1` FOREIGN KEY (`chapter_id`) REFERENCES `chapter` (`chapter_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `student`
--
ALTER TABLE `student`
  ADD CONSTRAINT `student_ibfk_1` FOREIGN KEY (`user_email`) REFERENCES `user` (`user_email`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
