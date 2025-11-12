
<?php
// This script can be run daily (via cron job) to automatically add problems
// For now, it adds sample problems for the current date

include '../includes/config.php';

$today = date('Y-m-d');

// Sample problems that can be rotated
$sample_problems = [
    'leetcode' => [
        'easy' => [
            ['title' => 'Two Sum', 'url' => 'https://leetcode.com/problems/two-sum/', 'points' => 10],
            ['title' => 'Valid Parentheses', 'url' => 'https://leetcode.com/problems/valid-parentheses/', 'points' => 10],
            ['title' => 'Merge Two Sorted Lists', 'url' => 'https://leetcode.com/problems/merge-two-sorted-lists/', 'points' => 10],
            ['title' => 'Remove Duplicates from Sorted Array', 'url' => 'https://leetcode.com/problems/remove-duplicates-from-sorted-array/', 'points' => 10]
        ],
        'medium' => [
            ['title' => 'Add Two Numbers', 'url' => 'https://leetcode.com/problems/add-two-numbers/', 'points' => 20],
            ['title' => 'Longest Substring Without Repeating Characters', 'url' => 'https://leetcode.com/problems/longest-substring-without-repeating-characters/', 'points' => 20],
            ['title' => 'Container With Most Water', 'url' => 'https://leetcode.com/problems/container-with-most-water/', 'points' => 20],
            ['title' => 'Three Sum', 'url' => 'https://leetcode.com/problems/3sum/', 'points' => 20]
        ],
        'hard' => [
            ['title' => 'Median of Two Sorted Arrays', 'url' => 'https://leetcode.com/problems/median-of-two-sorted-arrays/', 'points' => 30],
            ['title' => 'Regular Expression Matching', 'url' => 'https://leetcode.com/problems/regular-expression-matching/', 'points' => 30],
            ['title' => 'Merge k Sorted Lists', 'url' => 'https://leetcode.com/problems/merge-k-sorted-lists/', 'points' => 30],
            ['title' => 'Trapping Rain Water', 'url' => 'https://leetcode.com/problems/trapping-rain-water/', 'points' => 30]
        ]
    ],
    'hackerrank' => [
        'easy' => [
            ['title' => 'Simple Array Sum', 'url' => 'https://www.hackerrank.com/challenges/simple-array-sum/', 'points' => 10],
            ['title' => 'Compare the Triplets', 'url' => 'https://www.hackerrank.com/challenges/compare-the-triplets/', 'points' => 10],
            ['title' => 'A Very Big Sum', 'url' => 'https://www.hackerrank.com/challenges/a-very-big-sum/', 'points' => 10],
            ['title' => 'Diagonal Difference', 'url' => 'https://www.hackerrank.com/challenges/diagonal-difference/', 'points' => 10]
        ],
        'medium' => [
            ['title' => 'Climbing the Leaderboard', 'url' => 'https://www.hackerrank.com/challenges/climbing-the-leaderboard/', 'points' => 20],
            ['title' => 'The Hurdle Race', 'url' => 'https://www.hackerrank.com/challenges/the-hurdle-race/', 'points' => 20],
            ['title' => 'Designer PDF Viewer', 'url' => 'https://www.hackerrank.com/challenges/designer-pdf-viewer/', 'points' => 20],
            ['title' => 'Utopian Tree', 'url' => 'https://www.hackerrank.com/challenges/utopian-tree/', 'points' => 20]
        ],
        'hard' => [
            ['title' => 'Matrix Layer Rotation', 'url' => 'https://www.hackerrank.com/challenges/matrix-rotation-algo/', 'points' => 30],
            ['title' => 'Red Knight\'s Shortest Path', 'url' => 'https://www.hackerrank.com/challenges/red-knights-shortest-path/', 'points' => 30],
            ['title' => 'Coin Change', 'url' => 'https://www.hackerrank.com/challenges/coin-change/', 'points' => 30],
            ['title' => 'Abbreviation', 'url' => 'https://www.hackerrank.com/challenges/abbr/', 'points' => 30]
        ]
    ]
];

// Check if problems already exist for today
$query = "SELECT COUNT(*) as count FROM daily_problems WHERE date = '$today'";
$result = mysqli_query($conn, $query);
$existing_count = mysqli_fetch_assoc($result)['count'];

if($existing_count == 0) {
    // Add one problem of each difficulty for each platform
    foreach($sample_problems as $platform => $difficulties) {
        foreach($difficulties as $difficulty => $problems) {
            // Pick a random problem from the list
            $random_problem = $problems[array_rand($problems)];
            
            $title = mysqli_real_escape_string($conn, $random_problem['title']);
            $url = mysqli_real_escape_string($conn, $random_problem['url']);
            $points = $random_problem['points'];
            
            $query = "INSERT INTO daily_problems (date, platform, difficulty, problem_title, problem_url, points) 
                      VALUES ('$today', '$platform', '$difficulty', '$title', '$url', $points)";
            
            mysqli_query($conn, $query);
        }
    }
    
    echo "Daily problems added for $today\n";
} else {
    echo "Problems already exist for $today\n";
}
?>
