-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Apr 27, 2025 at 10:06 AM
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
-- Database: `course_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `bookmark`
--

CREATE TABLE `bookmark` (
  `user_id` varchar(20) NOT NULL,
  `playlist_id` varchar(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `bookmark`
--

INSERT INTO `bookmark` (`user_id`, `playlist_id`) VALUES
('U003', 'C008'),
('U001', 'C006'),
('U001', 'C009'),
('U001', 'C010'),
('U001', 'C001');

-- --------------------------------------------------------

--
-- Table structure for table `comments`
--

CREATE TABLE `comments` (
  `id` varchar(20) NOT NULL,
  `content_id` varchar(20) NOT NULL,
  `user_id` varchar(20) NOT NULL,
  `tutor_id` varchar(20) NOT NULL,
  `comment` varchar(1000) NOT NULL,
  `date` date NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `comments`
--

INSERT INTO `comments` (`id`, `content_id`, `user_id`, `tutor_id`, `comment`, `date`) VALUES
('cVwYnEEmI9vKYS3DqZBe', 'V006', 'U003', 'T003', 'Thank you for this video', '2025-03-25'),
('mh7aT5bsvx5MlLLouWmd', 'V008', 'U001', 'T001', 'Can you upload more videos on css?', '2025-04-25'),
('680cc84424759', 'V012', 'U001', 'T004', 'Nice Video', '2025-04-26'),
('680cf0a4ae64c', 'V001', 'U001', 'T001', 'nice', '2025-04-26');

-- --------------------------------------------------------

--
-- Table structure for table `contact`
--

CREATE TABLE `contact` (
  `name` varchar(50) NOT NULL,
  `email` varchar(50) NOT NULL,
  `number` int(10) NOT NULL,
  `message` varchar(1000) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `contact`
--

INSERT INTO `contact` (`name`, `email`, `number`, `message`) VALUES
('Liana Everhart', 'liana@gmail.com', 51234567, 'Hi, I was wondering if you plan to upload any courses on digital marketing in the near future?'),
('Zane Holloway', 'zane@gmail.com', 50099678, 'Hello, I’m interested in becoming a tutor at Virtu-Learn. Could you please share the requirements?');

-- --------------------------------------------------------

--
-- Table structure for table `content`
--

CREATE TABLE `content` (
  `id` varchar(20) NOT NULL,
  `tutor_id` varchar(20) NOT NULL,
  `playlist_id` varchar(20) NOT NULL,
  `title` varchar(100) NOT NULL,
  `description` varchar(1000) NOT NULL,
  `video` varchar(100) NOT NULL,
  `thumb` varchar(100) NOT NULL,
  `date` date NOT NULL DEFAULT current_timestamp(),
  `status` varchar(20) NOT NULL DEFAULT 'deactive'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `content`
--

INSERT INTO `content` (`id`, `tutor_id`, `playlist_id`, `title`, `description`, `video`, `thumb`, `date`, `status`) VALUES
('V001', 'T001', 'C001', 'HTML Basics', 'Learn the fundamentals of HTML', 'HTML in 5 minutes.mp4', 'HTML1.png', '2025-02-26', 'active'),
('V002', 'T001', 'C001', 'CSS Basics', 'Learn the fundamentals of CSS', 'CSS.mp4', 'CSS.jpg', '2025-02-26', 'active'),
('V003', 'T001', 'C001', 'JavaScript', 'Learn the fundamentals of JavaScript, the programming language that powers interactive web experiences. This video covers key concepts such as variables, functions, loops, and event handling to help you get started with JavaScript development.', 'JS.mp4', 'JS.png', '2025-02-26', 'active'),
('V004', 'T003', 'C002', 'Fundamental of design', 'Learn the core principles of design, including balance, contrast, alignment, and hierarchy. This video covers essential concepts to help you create visually appealing and effective designs, whether for graphic design, UI/UX, or any creative project.', 'Fundamentals.mp4', 'Fundamentals.jpg', '2025-02-26', 'active'),
('V005', 'T003', 'C002', 'Principles of Design', 'Learn the core principles of design that every designer should understand. This video covers essential concepts like balance, contrast, emphasis, movement, and unity, providing you with the tools to create visually compelling and effective designs.', 'Principles_of_Design.mp4', 'Principles.jpg', '2025-02-26', 'active'),
('V006', 'T003', 'C002', 'Layout & Composition', 'Discover the importance of layout and composition in design. This video breaks down how to arrange visual elements effectively to create harmony, balance, and flow in your designs. Learn about grid systems, alignment, hierarchy, and spacing, and how they work together to guide the viewer eye and communicate your message clearly. ', 'Layout & Composition.mp4', 'Layout.jpg', '2025-02-26', 'active'),
('V007', 'T003', 'C002', 'Typography', 'Explore the art of typography and learn how to choose the right fonts, manage spacing, and create visually appealing text layouts. This video covers the basics of typography to help enhance readability and design impact.', 'Typography.mp4', 'Typography.jpg', '2025-02-26', 'active'),
('V008', 'T001', 'C007', 'Advanced CSS', 'Master advanced CSS techniques in this video, where we dive into concepts like flexbox, grid layout, animations, transitions, and responsive design. Learn how to create dynamic, modern web layouts and enhance user experience with advanced styling methods.', 'AdvanceCSS.mp4', 'Advance_CSS.jpg', '2025-02-26', 'active'),
('V009', 'T001', 'C007', 'Hoisting in JavaScript', 'Learn about hoisting in JavaScript, a fundamental concept where variable and function declarations are moved to the top of their scope during execution. This video explains how hoisting works, the difference between var, let, and const, and how it impacts your code’s behavior.', 'HoistingJS.mp4', 'HoistingJS.avif', '2025-02-26', 'active'),
('V010', 'T003', 'C008', 'What is Branding?', 'Branding is more than just a logo—it’s the identity of a business. It shapes how people perceive a company, builds trust, and creates emotional connections with customers. In this video, we break down the key elements of branding, from visual identity to brand voice, and explain why strong branding is essential for success. ', 'Branding.mp4', 'Branding.jpg', '2025-02-26', 'active'),
('V011', 'T003', 'C008', 'Logo Design', 'A logo is the face of a brand—simple, memorable, and impactful. In this video, we’ll explore the key elements of great logo design and how it helps build brand identity.', 'Logo.mp4', 'logoDesign.jpg', '2025-02-26', 'active'),
('V012', 'T004', 'C009', 'What is Tax Compliance?', 'In this video, we’ll explain what tax compliance means, why it matters, and how to stay compliant with tax laws.', 'Tax.mp4', 'Tax.png', '2025-03-21', 'active'),
('V013', 'T005', 'C010', 'What is Criminal Law?', 'In this video, we’ll explain what criminal law means.', 'Criminal1.mp4', 'criminal.jpg', '2025-03-21', 'active'),
('V014', 'T005', 'C010', 'Criminal Law Basics', 'Criminal law defines offenses, rights, and penalties in the justice system. In this video, we’ll break down key concepts, types of crimes, and how the legal process works.', 'Criminal2.mp4', 'criminal1.jpg', '2025-03-21', 'active'),
('V015', 'T007', 'C012', 'Ethical Hacking Roadmap', 'Want to become an ethical hacker? This video outlines the step-by-step roadmap to mastering ethical hacking, from beginner to expert. ', 'Hacking1.mp4', 'hacking1.png', '2025-03-21', 'active'),
('V016', 'T007', 'C012', 'Password Cracking', 'Passwords are the first line of defense in cybersecurity, but hackers have ways to crack them. In this video, we’ll explore common password cracking techniques and how you can protect yourself. ', 'Hacking2.mp4', 'hacking2.jpg', '2025-03-21', 'active'),
('V017', 'T004', 'C003', 'Accounting Basics', 'Accounting is the language of business, tracking financial transactions and ensuring transparency. In this video, we’ll cover the fundamental principles of accounting, key terms, and how they apply to businesses. ', 'Accounting1.mp4', 'accounting1.webp', '2025-03-21', 'active'),
('V018', 'T004', 'C003', 'Recording Transactions', 'Recording transactions is essential for keeping accurate financial records. In this video, we’ll walk you through the process of documenting business transactions and how they impact your financial statements. ', 'Accounting2.mp4', 'accounting2.webp', '2025-03-21', 'active'),
('V019', 'T006', 'C005', 'Basics of Journalism', 'Journalism is about gathering, verifying, and reporting news. In this course, you will learn the core principles of journalism, from news writing to ethical reporting.', 'Journalism1.mp4', 'Journalism1.jpeg', '2025-03-21', 'active'),
('V020', 'T006', 'C005', 'Journalism with AI', 'AI is transforming journalism, making reporting faster and more efficient. This course explores how AI tools can assist journalists in research, content creation, and fact-checking.', 'journalism2.mp4', 'Journalism2.webp', '2025-03-21', 'active'),
('V021', 'T006', 'C005', 'How to Become an Investigative Reporter', 'Investigative journalism uncovers hidden truths and exposes important stories. This course teaches you how to research, analyze, and report on complex issues with accuracy and integrity.', 'Journalism3.mp4', 'Journalism3.jpg', '2025-03-21', 'active'),
('V022', 'T007', 'C006', 'What Are Cybersecurity Fundamentals?', 'Cybersecurity is essential for protecting data, networks, and systems from cyber threats. In this video, we’ll cover the core principles of cybersecurity and why they matter in today’s digital world.', 'Cybersecurity.mp4', 'cybersecurity1.jpg', '2025-03-21', 'active'),
('V023', 'T006', 'C011', 'Creating a News Report', 'A great news report informs, engages, and delivers facts accurately. In this video, we’ll guide you through the steps of crafting a compelling news story from start to finish.', 'NewsVId.mp4', 'News.jpg', '2025-03-21', 'active');

-- --------------------------------------------------------

--
-- Table structure for table `likes`
--

CREATE TABLE `likes` (
  `user_id` varchar(20) NOT NULL,
  `tutor_id` varchar(20) NOT NULL,
  `content_id` varchar(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `likes`
--

INSERT INTO `likes` (`user_id`, `tutor_id`, `content_id`) VALUES
('U003', 'T003', 'V004'),
('U003', 'T003', 'V005'),
('U003', 'T003', 'V006'),
('U004', 'T006', 'V019'),
('U004', 'T006', 'V020'),
('U001', 'T003', 'V005'),
('U001', 'T007', 'V022');

-- --------------------------------------------------------

--
-- Table structure for table `playlist`
--

CREATE TABLE `playlist` (
  `id` varchar(20) NOT NULL,
  `tutor_id` varchar(20) NOT NULL,
  `title` varchar(100) NOT NULL,
  `description` varchar(1000) NOT NULL,
  `thumb` varchar(100) NOT NULL,
  `date` date NOT NULL DEFAULT current_timestamp(),
  `status` varchar(20) NOT NULL DEFAULT 'deactive'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `playlist`
--

INSERT INTO `playlist` (`id`, `tutor_id`, `title`, `description`, `thumb`, `date`, `status`) VALUES
('C001', 'T001', 'Web Design Basics', 'Learn the fundamentals of modern web design using HTML, CSS, and responsive design techniques.', 'web_design.jpg', '2025-02-26', 'active'),
('C002', 'T003', 'Graphic Design Mastery', 'Explore advanced techniques in Photoshop, Illustrator, and UI/UX design.', 'graphic_design.jpg', '2025-02-26', 'active'),
('C004', 'T005', 'Introduction to Corporate Law', 'Learn the basics of corporate law, contracts, and business regulations.', 'corporate_law.webp', '2024-01-15', 'active'),
('C003', 'T004', 'Accounting Essentials', 'Understand core accounting principles, bookkeeping, and financial statements.', 'financial_accounting.jpg', '2024-01-15', 'active'),
('C005', 'T006', 'Investigative Journalism', 'Master the art of investigative reporting, research techniques, and ethical journalism.', 'journalism.jpg', '2024-01-15', 'active'),
('C006', 'T007', 'Cybersecurity Fundamentals', 'Understand key principles of cybersecurity, including network security and cryptography.', 'cybersecurity.jpg', '2024-01-15', 'active'),
('C007', 'T001', 'Advanced CSS & JavaScript', 'Enhance your web design skills with animations, Flexbox, and modern JS frameworks.', 'advanced_css_js.jpg', '2025-02-26', 'active'),
('C008', 'T003', 'Branding & Logo Design', 'Learn how to create compelling brand identities and logos using Adobe Illustrator.', 'branding_logo.jpg', '2025-02-26', 'active'),
('C009', 'T004', 'Taxation & Compliance', 'Understand the fundamentals of tax laws, compliance, and financial regulations.', 'tax_compliance.jpg', '2025-02-26', 'active'),
('C010', 'T005', 'Criminal Law Basics', 'Explore criminal law principles, case studies, and court procedures.', 'criminal_law.jpg', '2025-02-26', 'active'),
('C011', 'T006', 'News Reporting', 'Master ethical journalism, news writing, and fact-checking techniques.', 'news_reporting.jpg', '2024-01-15', 'active'),
('C012', 'T007', 'Ethical Hacking', 'Learn ethical hacking techniques, penetration testing, and cybersecurity measures.', 'ethical_hacking.jpg', '2025-02-26', 'active');

-- --------------------------------------------------------

--
-- Table structure for table `tutors`
--

CREATE TABLE `tutors` (
  `id` varchar(20) NOT NULL,
  `name` varchar(50) NOT NULL,
  `profession` varchar(50) NOT NULL,
  `email` varchar(50) NOT NULL,
  `password` varchar(50) NOT NULL,
  `image` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tutors`
--

INSERT INTO `tutors` (`id`, `name`, `profession`, `email`, `password`, `image`) VALUES
('T001', 'Tom Holland', 'Designer', 'tom@gmail.com', '96835dd8bfa718bd6447ccc87af89ae1675daeca', 'tutor1.jpg'),
('T003', 'Alice Johnson', 'designer', 'alice@gmail.com', '522b276a356bdf39013dfabea2cd43e141ecc9e8', 'tutor2.jpg'),
('T004', 'Liam Jackson', 'accountant', 'liam@gmail.com', '3c5e7eab99ca6c994607f4051b2fb6d9f7c7001a', 'tutor4.jpg'),
('T005', 'Sarah Williams', 'lawyer', 'sarah@gmail.com', 'be8ec20d52fdf21c23e83ba2bb7446a7fecb32ac', 'tutor5.jpg'),
('T006', 'Michael Brown', 'journalist', 'michael@gmail.com', '17b9e1c64588c7fa6419b4d29dc1f4426279ba01', 'tutor6.avif'),
('T007', 'Emma Wilson', 'photographer', 'emma@gmail.com', 'efdb8f7f2fe9c47e34dfe1fb7c491d0638ec2d86', 'tutor3.jpg');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` varchar(20) NOT NULL,
  `name` varchar(50) NOT NULL,
  `email` varchar(50) NOT NULL,
  `password` varchar(50) NOT NULL,
  `image` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `name`, `email`, `password`, `image`) VALUES
('U001', 'Liana Everhart', 'liana@gmail.com', '3b7a24be9a4472335b37c7f295f9858b265a0c67', 'user1.avif'),
('U002', 'Sienna Vexley', 'sienna@gmail.com', 'e35b78f98a7d5e97029a32c16b016a7dbc23ab86', 'user2.avif'),
('U003', 'Orion Blackwood', 'orian@gmail.com', '96217b97ef1462856456e275dda9854480e15cc8', 'user3.jpg'),
('U004', 'Zane Holloway', 'zane@gmail.com', 'e1e8050e81eb73325220610ce0cf70f1b0a5403b', 'user4.jpg'),
('U005', 'Aria Durnham', 'aria@gmail.com', 'e53e8ae89e2a62a3e9bc98b81c9373ccb9941041', 'user7.webp'),
('U006', 'Naya Lysander', 'naya@gmail.com', '457dc0d904f2478116ad4964df28a4d6ee4d616a', 'user8.jpg'),
('U007', 'Kael Winthrop', 'kael@gmail.com', '94fe1b58a75101272a57efa6993f089d0e56d62f', 'user5.jpg'),
('U008', 'Maddox Valtieri', 'maddox@gmail.com', 'de9f75fad4cd1d010082c6703912465eb1337a7c', 'user4.jpg');
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
