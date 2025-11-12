
-- Daily Problems Table
CREATE TABLE `daily_problems` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `date` date NOT NULL,
  `platform` enum('leetcode','hackerrank') NOT NULL,
  `difficulty` enum('easy','medium','hard') NOT NULL,
  `problem_title` varchar(255) NOT NULL,
  `problem_url` varchar(500) NOT NULL,
  `points` int(11) NOT NULL,
  `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_daily_problem` (`date`, `platform`, `difficulty`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Problem Submissions Table
CREATE TABLE `problem_submissions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `student_id` int(11) NOT NULL,
  `problem_id` int(11) NOT NULL,
  `submission_file` varchar(255) NOT NULL,
  `status` enum('pending','approved','rejected') DEFAULT 'pending',
  `points_awarded` int(11) DEFAULT 0,
  `admin_comment` text,
  `submitted_at` timestamp DEFAULT CURRENT_TIMESTAMP,
  `reviewed_at` timestamp NULL DEFAULT NULL,
  `reviewed_by` int(11) NULL,
  PRIMARY KEY (`id`),
  FOREIGN KEY (`student_id`) REFERENCES `students`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`problem_id`) REFERENCES `daily_problems`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`reviewed_by`) REFERENCES `admins`(`id`) ON DELETE SET NULL,
  UNIQUE KEY `unique_submission` (`student_id`, `problem_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Insert sample daily problems
INSERT INTO `daily_problems` (`date`, `platform`, `difficulty`, `problem_title`, `problem_url`, `points`) VALUES
(CURDATE(), 'leetcode', 'easy', 'Two Sum', 'https://leetcode.com/problems/two-sum/', 10),
(CURDATE(), 'leetcode', 'medium', 'Add Two Numbers', 'https://leetcode.com/problems/add-two-numbers/', 20),
(CURDATE(), 'leetcode', 'hard', 'Median of Two Sorted Arrays', 'https://leetcode.com/problems/median-of-two-sorted-arrays/', 30),
(CURDATE(), 'hackerrank', 'easy', 'Simple Array Sum', 'https://www.hackerrank.com/challenges/simple-array-sum/', 10),
(CURDATE(), 'hackerrank', 'medium', 'Climbing the Leaderboard', 'https://www.hackerrank.com/challenges/climbing-the-leaderboard/', 20),
(CURDATE(), 'hackerrank', 'hard', 'Matrix Layer Rotation', 'https://www.hackerrank.com/challenges/matrix-rotation-algo/', 30);
