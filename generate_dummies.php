<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once 'secrets.php';

$conn = new mysqli($db_host, $db_user, $db_pass, $db_name);

if ($conn->connect_error) {
    die("<div style=\"font-family: 'Poppins', sans-serif;\">Connection failed: " . $conn->connect_error . "</div>");
}

$event_id = isset($_GET['event_id']) ? (int)$_GET['event_id'] : 1; 
$limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 10;

$cats = [];
$res = $conn->query("SELECT sport_type, category_name, fee FROM event_categories WHERE event_id = $event_id");
if ($res) {
    while($row = $res->fetch_assoc()) {
        $cats[] = $row;
    }
}

$first_names = [
    'Joyce', 'Leonard Damong jr', 'Vazir', 'Kurt', 'Michael', 'Ethan', 'Jemuel', 'Mark', 'JOHANNES', 'Bonifacio', 
    'Mat', 'Zechariah', 'Kurt', 'Ally', 'mohamad ali', 'velascoroseann', 'Nathaniel', 'chris', 'Carl Justine', 
    'Xadrian Matthew', 'Nicolas Brein A.', 'evo semic', 'kaito', 'cherzed', 'Jilliana', 'bianca beyonce', 
    'Colleen', 'Lavigne', 'Kyle', 'Ali', 'Chiradee', 'karl', 'Cy', 'Aidan Ziya', 'Sean', 'Deon', 'Bryce', 
    'Sherry', 'Jelmar Anne A.', 'Mhie', 'Jelmar', 'Ezriah', 'Federico', 'Kristel de', 'Shayra', 'Shayra', 
    'Ezriah', 'Danji', 'ZEN', 'Kvana', 'Shan', 'Princess Brylle N', 'Aerianne', 'Jazzelle', 'Hannah Gail', 
    'gabrielle', 'mark julius san', 'Dharel', 'HOMBREBUENO FRITZER', 'Hyacinth', 'Emerson', 'Craeven Marc C.', 
    'Dreick', 'fritzer', 'Joyce', 'James', 'crae', 'Dharel', 'Jasmine', 'matthew jastine L.', 'Raphael Matthew', 
    'mark san', 'Dennielle', 'Jovenele', 'Dreick', 'luis', 'Paul Dean Mark S.', 'Marvin San', 'Paul Dean Mark S.', 
    'Jeric Lemonayde', 'craeven Marc C.', 'Evo', 'Jovany', 'Alia', 'Adrian Dantly B.', 'Adriel De', 'Alex', 
    'Andrea', 'Angelo', 'Axel', 'Carl', 'Cristoff', 'Curt Jervy', 'Derryl Hanes', 'Ermo', 'Jonark', 'Faith', 
    'Evo', 'Nathan', 'Jeferson Llaneta', 'Jericho', 'John Hnery', 'John Michael', 'Kishi', 'Micka', 'Ryama', 
    'Mark Gregory', 'Nathan', 'Yoram', 'Cristoff', 'Gab', 'Ryan', 'Adirel De', 'BronsonBagalay', 'Bryce'
];

$last_names = [
    'Tayaban', 's', 'Paolo', 'Corpuz', 'DUQUE', 'Garcia', 'malana', 'Ortiz', 'batua', 'Quirante', 'rosal', 
    'demot', 'Manalo', 'Patalinghug', 'cabanban', 'espeleta', 'Camatayan', 'camatayan', 'Reinoso', 'Reinoso', 
    'Togana', 'Domingo', 'Dominic', 'Lou', 'Bested', 'Biason', 'Bested', 'Martinez', 'Eden', 'Villa', 'Bawayan', 
    'Bawayan', 'Martinez', 'Rifani', 'elwas', 'Bangnan', 'Tadena', 'Warren', 'juan', 'P', 'Leigh', 'Reduca', 
    'Daileg', 'Dangatan', 'hombrebueno', 'Tayaban', 'bahilango', 'Orlino', 'juan', 'Paddison', 'Santos', 'Pila', 
    'Juan', 'Pila', 'Aspuria', 'Daileg', 'Semic', 'Palabay', 'Pundogar', 'Dato', 'Luna', 'Guimayen', 'Castillo', 
    'Aquino', 'Antonio', 'Lhein', 'Lawan', 'Bonifacio', 'Segundo', 'Blaza', 'Estocio', 'Tolentino', 'Cabanban', 
    'Gayog', 'Mendoza', 'Valdez', 'Danao', 'Ico', 'Dariano', 'Rhenziel', 'Alani', 'Andrada', 'Lansang', 'Blake', 
    'Ramos', 'Luna', 'Tamingo'
];
$genders = ['Male', 'Female'];

$query = "INSERT INTO event_registrations (event_id, name, email, phone, age, gender, category, status, token, registration_date, registration_time, paymongo_id) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
$stmt = $conn->prepare($query);

for ($i = 0; $i < $limit; $i++) {
    $name = $first_names[array_rand($first_names)] . ' ' . $last_names[array_rand($last_names)];
    $email = strtolower(str_replace(' ', '.', $name)) . rand(10,99) . '@gmail.com';
    $phone = '09' . rand(100000000, 999999999);
    $age = rand(15, 35);
    $gender = $genders[array_rand($genders)];
    
    $cat = !empty($cats) ? $cats[array_rand($cats)] : ['sport_type' => 'Inline', 'category_name' => 'Open', 'fee' => 0];
    $sport_type = ucfirst($cat['sport_type']); 
    $sub_category = $cat['category_name'];
    $combined_category = $sport_type . ' - ' . $sub_category;
    $fee = isset($cat['fee']) ? (float)$cat['fee'] : 0;
    
    $status = 'paid';
    $token = bin2hex(random_bytes(8));
    $random_timestamp = mt_rand(strtotime('-30 days'), time());
    $datetime_str = date('Y-m-d H:i:s', $random_timestamp);
    
    $paymongo_id = (rand(0, 1) === 1) ? 'cs_test_' . bin2hex(random_bytes(16)) : null;
    
    $stmt->bind_param("isssisssssss", $event_id, $name, $email, $phone, $age, $gender, $combined_category, $status, $token, $datetime_str, $datetime_str, $paymongo_id);
    $stmt->execute();
}

echo "<div style=\"font-family: 'Poppins', sans-serif; color: #25523B; font-weight: bold; text-align: center; margin-top: 50px;\">Successfully generated $limit dummy registrations for Event ID $event_id!</div>";

$stmt->close();
$conn->close();
?>