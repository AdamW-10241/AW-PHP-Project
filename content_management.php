<?php
session_start();
require_once 'config.php';
require_once 'vendor/autoload.php';
require_once 'src/Account.php';
require_once 'src/Security.php';
require_once 'src/Game.php';

use Adam\AwPhpProject\Account;
use Adam\AwPhpProject\Security;
use Adam\AwPhpProject\Game;

// Initialize Twig
$loader = new \Twig\Loader\FilesystemLoader('templates');
$twig = new \Twig\Environment($loader);

// Add Security class to Twig globals
$twig->addGlobal('security', new Security());

// Check if user is logged in and is admin
$account = new Account();
if (!isset($_SESSION['email']) || !$account->getUserByEmail($_SESSION['email']) || !$account->isAdmin()) {
    header('Location: /login.php');
    exit();
}

// Handle form submission
$errors = [];
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validate CSRF token
    if (!Security::validateToken($_POST['csrf_token'] ?? '')) {
        $errors[] = "Invalid CSRF token";
    } else {
        $game = new Game();
        $action = $_POST['action'] ?? '';
        
        switch ($action) {
            case 'create':
                // Validate and process the form data
                $title = trim($_POST['title'] ?? '');
                $description = trim($_POST['description'] ?? '');
                $min_players = (int)($_POST['min_players'] ?? 0);
                $max_players = (int)($_POST['max_players'] ?? 0);
                $playtime = trim($_POST['playtime'] ?? '');
                $age = (int)($_POST['age'] ?? 0);
                $price = (float)($_POST['price'] ?? 0);
                $year = (int)($_POST['year'] ?? 0);
                
                // Process comma-separated lists
                $artists = array_filter(array_map('trim', explode(',', $_POST['artists'] ?? '')));
                $designers = array_filter(array_map('trim', explode(',', $_POST['designers'] ?? '')));
                $publishers = array_filter(array_map('trim', explode(',', $_POST['publishers'] ?? '')));
                
                // Validate required fields
                if (empty($title)) {
                    $errors[] = "Title is required";
                }
                if (empty($description)) {
                    $errors[] = "Description is required";
                }
                if ($min_players <= 0) {
                    $errors[] = "Minimum players must be greater than 0";
                }
                if ($max_players < $min_players) {
                    $errors[] = "Maximum players must be greater than or equal to minimum players";
                }
                if (empty($playtime)) {
                    $errors[] = "Playtime is required";
                }
                if ($age <= 0) {
                    $errors[] = "Age must be greater than 0";
                }
                if ($price <= 0) {
                    $errors[] = "Price must be greater than 0";
                }
                if ($year <= 0) {
                    $errors[] = "Year must be greater than 0";
                }
                
                // Handle image upload
                $image_path = '';
                if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
                    $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
                    $max_size = 5 * 1024 * 1024; // 5MB
                    
                    if (!in_array($_FILES['image']['type'], $allowed_types)) {
                        $errors[] = "Invalid image type. Only JPG, PNG, and GIF are allowed.";
                    } elseif ($_FILES['image']['size'] > $max_size) {
                        $errors[] = "Image size must be less than 5MB";
                    } else {
                        $extension = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
                        $upload_dir = 'assets/cover_images/';
                        if (!file_exists($upload_dir)) {
                            mkdir($upload_dir, 0777, true);
                        }
                        
                        // First upload the original file to a temporary location
                        $temp_path = $upload_dir . 'temp_' . uniqid() . '.' . $extension;
                        if (!move_uploaded_file($_FILES['image']['tmp_name'], $temp_path)) {
                            $errors[] = "Failed to upload image";
                        } else {
                            // Create the image from the uploaded file
                            switch ($extension) {
                                case 'jpg':
                                case 'jpeg':
                                    $source = imagecreatefromjpeg($temp_path);
                                    break;
                                case 'png':
                                    $source = imagecreatefrompng($temp_path);
                                    break;
                                case 'gif':
                                    $source = imagecreatefromgif($temp_path);
                                    break;
                                default:
                                    $errors[] = "Unsupported image format";
                                    unlink($temp_path);
                                    break;
                            }
                            
                            if (isset($source) && !$errors) {
                                // Get the new game ID by getting the last ID and adding 1
                                $db = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
                                $result = $db->query("SELECT MAX(id) as max_id FROM BoardGame");
                                $row = $result->fetch_assoc();
                                $new_id = ($row['max_id'] ?? 0) + 1;
                                
                                // Create the new filename
                                $new_filename = "board_{$new_id}.png";
                                $image_path = $upload_dir . $new_filename;
                                
                                // Convert and save as PNG
                                if (imagepng($source, $image_path)) {
                                    // Clean up the temporary file
                                    unlink($temp_path);
                                    // Free up memory
                                    imagedestroy($source);
                                } else {
                                    $errors[] = "Failed to convert image to PNG";
                                    unlink($temp_path);
                                }
                            }
                        }
                    }
                } else {
                    $errors[] = "Image is required";
                }
                
                // If no errors, create the game
                if (empty($errors)) {
                    if ($game->create($title, $description, $min_players, $max_players, $playtime, $age, $price, $image_path, $year, $artists, $designers, $publishers)) {
                        $success = "Game created successfully!";
                    } else {
                        $errors[] = "Failed to create game";
                    }
                }
                break;

            case 'delete':
                $game_id = (int)($_POST['game_id'] ?? 0);
                if ($game_id <= 0) {
                    $errors[] = "Invalid game ID";
                } else {
                    if ($game->delete($game_id)) {
                        $success = "Game deleted successfully!";
                    } else {
                        $errors[] = "Failed to delete game";
                    }
                }
                break;
        }
    }
}

// Get all games for the table
$game = new Game();
$games = $game->getAllGames();

// Debug output
error_log("Number of games fetched: " . count($games));
foreach ($games as $game) {
    error_log("Game: " . print_r($game, true));
}

// Render the template
echo $twig->render('content_management.twig', [
    'errors' => $errors,
    'success' => $success,
    'loggedin' => true,
    'is_admin' => true,
    'current_page' => 'content_management',
    'games' => $games
]); 