-- phpMyAdmin SQL Dump
-- version 5.2.3
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Sep 11, 2026 at 06:32 PM
-- Server version: 11.8.6-MariaDB
-- PHP Version: 8.4.16

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `fit2104_a3`
--
CREATE DATABASE IF NOT EXISTS `fit2104_a3` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `fit2104_a3`;

-- --------------------------------------------------------

--
-- Table structure for table `adoption_applications`
--

CREATE TABLE `adoption_applications` (
  `application_id` int(11) NOT NULL,
  `animal_id` int(11) NOT NULL,
  `applicant_name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `phone` varchar(20) NOT NULL,
  `suburb` varchar(100) DEFAULT NULL,
  `housing_type` varchar(50) DEFAULT NULL,
  `home_ownership` enum('own','rent','other') DEFAULT NULL,
  `other_pets` text DEFAULT NULL,
  `reason_for_adoption` text DEFAULT NULL,
  `application_date` timestamp NOT NULL DEFAULT current_timestamp(),
  `status` enum('new','under_review','approved','rejected') NOT NULL DEFAULT 'new'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `adoption_applications`
--

INSERT INTO `adoption_applications` (`application_id`, `animal_id`, `applicant_name`, `email`, `phone`, `suburb`, `housing_type`, `home_ownership`, `other_pets`, `reason_for_adoption`, `application_date`, `status`) VALUES
(1, 1, 'Rebecca Hall', 'rebecca.hall@example.com', '0416697848', 'Clayton', 'House', 'own', 'None', 'Looking for an active companion for weekend hikes.', '2026-08-19 16:49:26', 'new'),
(2, 2, 'Anthony Ford', 'anthony.ford@example.com', '0401845146', 'Glen Waverley', 'Apartment', 'rent', 'One cat', 'Want a small, calm dog suited to apartment living.', '2026-08-21 16:49:26', 'under_review'),
(3, 5, 'Jasmine Lee', 'jasmine.lee@example.com', '0427048281', 'Box Hill', 'House', 'own', 'None', 'First-time cat owner, has done plenty of research.', '2026-08-23 16:49:26', 'approved'),
(4, 4, 'Warren Sinclair', 'warren.sinclair@example.com', '0448932528', 'Dandenong', 'House', 'own', 'One dog', 'Wants a calm senior companion for an older dog.', '2026-07-28 16:49:26', 'approved'),
(5, 8, 'Nadia Farouk', 'nadia.farouk@example.com', '0480957015', 'Frankston', 'House', 'own', 'None', 'Has rabbit-proofed a section of the backyard already.', '2026-08-25 16:49:26', 'new'),
(6, 10, 'Callum Reid', 'callum.reid@example.com', '0443039117', 'Werribee', 'Apartment', 'rent', 'None', 'Wants a small pet suitable for a unit.', '2026-08-28 16:49:26', 'new'),
(7, 13, 'Georgia Marsh', 'georgia.marsh@example.com', '0418227824', 'Sunshine', 'House', 'own', 'One bird', 'Experienced bird owner looking to add a companion.', '2026-08-17 16:49:26', 'under_review'),
(8, 18, 'Peter Yin', 'peter.yin@example.com', '0489638346', 'Coburg', 'Acreage', 'own', 'Two goats', 'Has space and existing goats needing a companion.', '2026-08-07 16:49:26', 'new'),
(9, 6, 'Hana Suzuki', 'hana.suzuki@example.com', '0457871331', 'Richmond', 'Apartment', 'rent', 'None', 'Looking for a quiet, low-maintenance cat.', '2026-08-15 16:49:26', 'rejected'),
(10, 19, 'Michael Brennan', 'michael.brennan@example.com', '0450983930', 'Cranbourne', 'House', 'own', 'None', 'Long-time reptile keeper with proper enclosure ready.', '2026-08-04 16:49:26', 'approved'),
(11, 11, 'Priya Anand', 'priya.anand@example.com', '0410310518', 'Hawthorn', 'House', 'own', 'One hamster already', 'Wants a companion for her existing hamster setup.', '2026-08-31 16:49:26', 'new'),
(12, 1, 'Steven Cho', 'steven.cho@example.com', '0434738299', 'Doncaster', 'House', 'own', 'None', 'Family with a large yard looking for an active dog.', '2026-09-01 16:49:26', 'new'),
(13, 2, 'Laura Kim', 'laura.kim@example.com', '0473763116', 'Preston', 'House', 'rent', 'None', 'Wants a small, gentle dog for an elderly parent.', '2026-09-03 16:49:26', 'new'),
(14, 5, 'Daniel Osei', 'daniel.osei@example.com', '0456670106', 'Ringwood', 'House', 'own', 'One dog', 'Looking for a friendly kitten to join the household.', '2026-09-04 16:49:26', 'under_review'),
(15, 8, 'Zara Ahmed', 'zara.ahmed@example.com', '0451333872', 'Brunswick', 'Apartment', 'rent', 'None', 'Has kept rabbits before, well set up for one.', '2026-09-05 16:49:26', 'new');

-- --------------------------------------------------------

--
-- Table structure for table `animals`
--

CREATE TABLE `animals` (
  `animal_id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `breed_id` int(11) NOT NULL,
  `sex` enum('male','female','unknown') NOT NULL DEFAULT 'unknown',
  `desexed` tinyint(1) NOT NULL DEFAULT 0,
  `date_of_birth` date DEFAULT NULL,
  `date_admitted` date NOT NULL,
  `description` text DEFAULT NULL,
  `medical_notes` text DEFAULT NULL,
  `status` enum('in_care','available','pending','adopted') NOT NULL DEFAULT 'in_care',
  `profile_image` varchar(255) DEFAULT NULL,
  `foster_carer_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `animals`
--

INSERT INTO `animals` (`animal_id`, `name`, `breed_id`, `sex`, `desexed`, `date_of_birth`, `date_admitted`, `description`, `medical_notes`, `status`, `profile_image`, `foster_carer_id`) VALUES
(1, 'Buddy', 1, 'male', 1, '2021-03-14', '2026-07-29', 'Friendly Labrador who loves fetch and swimming.', NULL, 'available', 'animal_profiles/animal_6aa21c413e6b8.jpg', NULL),
(2, 'Luna', 2, 'female', 1, '2022-07-01', '2026-08-13', 'Small, timid Chihuahua - needs a quiet home.', 'Mild heart murmur, monitored by vet.', 'available', 'animal_profiles/animal_6aa220a119cc5.jpg', NULL),
(3, 'Coco', 3, 'female', 0, NULL, '2026-08-28', 'Playful Poodle mix, still a puppy at heart.', NULL, 'in_care', 'animal_profiles/animal_6aa21f76969b8.jpg', 1),
(4, 'Max', 4, 'male', 1, '2019-11-20', '2026-06-09', 'Senior mixed-breed dog, calm and well-behaved.', 'Mild arthritis in hind legs.', 'adopted', 'animal_profiles/animal_6aa220d58acc2.jpg', NULL),
(5, 'Milo', 5, 'male', 1, '2023-02-05', '2026-08-23', 'Curious Domestic Shorthair kitten.', NULL, 'available', 'animal_profiles/animal_6aa3bc9a6a2e6.jpg', 5),
(6, 'Bella', 6, 'female', 1, '2020-05-18', '2026-07-09', 'Elegant Ragdoll, enjoys quiet laps and sunbeams.', NULL, 'pending', 'animal_profiles/animal_6aa2160c01baf.jpg', 9),
(7, 'Whiskers', 7, 'male', 0, NULL, '2026-09-02', 'Recently admitted stray cat, still settling in.', 'Being treated for mild ear mites.', 'in_care', 'animal_profiles/animal_6aa2223fdd04a.jpg', NULL),
(8, 'Thumper', 8, 'male', 1, '2022-09-09', '2026-08-05', 'Holland Lop rabbit, litter-trained and social.', NULL, 'available', 'animal_profiles/animal_6aa2221ca1076.jpg', 10),
(9, 'Clover', 9, 'female', 0, NULL, '2026-08-18', 'Mixed-breed rabbit, a little shy around new people.', NULL, 'in_care', 'animal_profiles/animal_6aa216658d9d7.jpeg', NULL),
(10, 'Nibbles', 11, 'male', 0, NULL, '2026-08-30', 'American Guinea Pig, very vocal and food-motivated.', NULL, 'available', 'animal_profiles/animal_6aa221773974c.jpg', 3),
(11, 'Pepper', 13, 'female', 0, NULL, '2026-08-26', 'Syrian hamster, nocturnal and active at night.', NULL, 'available', 'animal_profiles/animal_6aa22192bd1c1.jpg', 3),
(12, 'Frodo', 15, 'male', 1, '2021-01-11', '2026-07-24', 'Standard ferret, playful and needs an enclosed yard.', NULL, 'in_care', 'animal_profiles/animal_6aa21fd4e1736.jpg', NULL),
(13, 'Sunny', 17, 'male', 0, NULL, '2026-08-16', 'Budgerigar, hand-reared and used to being handled.', NULL, 'available', 'animal_profiles/animal_6aa2220626871.jpg', 7),
(14, 'Kiwi', 18, 'female', 0, '2023-04-01', '2026-08-20', 'Cockatiel, whistles along to music.', NULL, 'available', 'animal_profiles/animal_6aa22030bcf8f.jpg', 7),
(15, 'Henrietta', 20, 'female', 0, NULL, '2026-08-08', 'Isa Brown chicken, good layer, friendly with people.', NULL, 'in_care', 'animal_profiles/animal_6aa22008db3ba.jpg', NULL),
(16, 'Quackers', 22, 'male', 0, NULL, '2026-08-11', 'Pekin duck, part of a small rescued flock.', NULL, 'available', 'animal_profiles/animal_6aa221cc76048.jpg', NULL),
(17, 'Billy', 24, 'male', 1, '2020-06-30', '2026-07-19', 'Nubian goat, needs acreage and a companion animal.', NULL, 'in_care', 'animal_profiles/animal_6aa2163572baf.jpg', 12),
(18, 'Dolly', 26, 'female', 1, '2019-08-12', '2026-06-29', 'Merino sheep, surrendered from a small hobby farm.', NULL, 'available', 'animal_profiles/animal_6aa21f913180d.jpg', 12),
(19, 'Franklin', 30, 'male', 0, '2015-01-01', '2026-05-30', 'Sulcata tortoise, requires specialised heating setup.', NULL, 'available', 'animal_profiles/animal_6aa21fadde638.jpg', 15),
(20, 'Monty', 32, 'male', 0, NULL, '2026-08-24', 'Corn snake, easy-going temperament, feeds well.', NULL, 'in_care', 'animal_profiles/animal_6aa2214abdc97.jpg', 15),
(21, 'Lilly', 1, 'male', 1, '2025-06-16', '2026-09-08', 'A sample dog manually inputted', NULL, 'in_care', 'animal_profiles/animal_6aa2205305199.jpg', NULL),
(22, 'Maxipad', 5, 'male', 0, '2025-10-02', '2026-09-08', 'A manually inputted animal', 'None', 'in_care', 'animal_profiles/animal_6a9f3d54b2dd3.jpg', 12);

-- --------------------------------------------------------

--
-- Table structure for table `breeds`
--

CREATE TABLE `breeds` (
  `breed_id` int(11) NOT NULL,
  `species_id` int(11) NOT NULL,
  `breed_name` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `breeds`
--

INSERT INTO `breeds` (`breed_id`, `species_id`, `breed_name`) VALUES
(2, 1, 'Chihuahua'),
(1, 1, 'Labrador'),
(4, 1, 'Mixed / Unknown'),
(3, 1, 'Poodle'),
(5, 2, 'Domestic Shorthair'),
(7, 2, 'Mixed / Unknown'),
(6, 2, 'Ragdoll'),
(8, 3, 'Holland Lop'),
(9, 3, 'Mixed / Unknown'),
(10, 4, 'Unknown'),
(11, 5, 'American'),
(12, 5, 'Mixed / Unknown'),
(14, 6, 'Mixed / Unknown'),
(13, 6, 'Syrian'),
(16, 7, 'Mixed / Unknown'),
(15, 7, 'Standard'),
(17, 8, 'Budgerigar'),
(18, 8, 'Cockatiel'),
(19, 8, 'Mixed / Unknown'),
(20, 9, 'Isa Brown'),
(21, 9, 'Mixed / Unknown'),
(23, 10, 'Mixed / Unknown'),
(22, 10, 'Pekin'),
(25, 11, 'Mixed / Unknown'),
(24, 11, 'Nubian'),
(26, 12, 'Merino'),
(27, 12, 'Mixed / Unknown'),
(28, 13, 'Kunekune'),
(29, 13, 'Mixed / Unknown'),
(31, 14, 'Mixed / Unknown'),
(30, 14, 'Sulcata'),
(32, 15, 'Corn Snake'),
(33, 15, 'Mixed / Unknown');

-- --------------------------------------------------------

--
-- Table structure for table `contact_messages`
--

CREATE TABLE `contact_messages` (
  `message_id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `message` text NOT NULL,
  `submitted_date` timestamp NOT NULL DEFAULT current_timestamp(),
  `replied` tinyint(1) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `contact_messages`
--

INSERT INTO `contact_messages` (`message_id`, `name`, `email`, `phone`, `message`, `submitted_date`, `replied`) VALUES
(1, 'Karen Millar', 'karen.millar@example.com', '0462473178', 'Hi, do you accept surrendered guinea pigs? We can no longer care for ours.', '2026-08-12 16:49:26', 1),
(2, 'Tom Everly', 'tom.everly@example.com', '0410801326', 'Wondering if Buddy the Labrador is good with young children?', '2026-08-17 16:49:26', 1),
(3, 'Fiona Grant', 'fiona.grant@example.com', '0477360260', 'I would like to volunteer as a weekend dog walker, who do I contact?', '2026-08-18 16:49:26', 0),
(4, 'Marcus Bell', 'marcus.bell@example.com', '0464746872', 'Do you take donations of unopened pet food and bedding?', '2026-08-20 16:49:26', 1),
(5, 'Yasmin Haddad', 'yasmin.haddad@example.com', '0434309805', 'Is there an adoption fee for the chickens currently listed?', '2026-08-22 16:49:26', 0),
(6, 'Connor Blake', 'connor.blake@example.com', '0400978820', 'My application from last week hasn\'t had a response yet, please advise.', '2026-08-24 16:49:26', 0),
(7, 'Petra Novak', 'petra.novak@example.com', '0481219136', 'Can I organise a visit to meet Bella the Ragdoll before applying?', '2026-08-26 16:49:26', 1),
(8, 'Aiden Fitzgerald', 'aiden.fitzgerald@example.com', '0419399091', 'Do you have any foster carer info sessions coming up?', '2026-08-28 16:49:26', 0),
(9, 'Grace Okafor', 'grace.okafor@example.com', '0469985435', 'Are the tortoises suitable for a home with young kids?', '2026-08-29 16:49:26', 1),
(10, 'Harvey Lin', 'harvey.lin@example.com', '0434624751', 'I saw Monty the corn snake listed - is he still available?', '2026-08-31 16:49:26', 0),
(11, 'Simone Wright', 'simone.wright@example.com', '0407991183', 'Do you partner with any local vet clinics for discounted checkups?', '2026-09-01 16:49:26', 1),
(12, 'Adam Kowalski', 'adam.kowalski@example.com', '0484251354', 'Interested in fostering farm animals, do you have any acreage requirements?', '2026-09-02 16:49:26', 0),
(13, 'Layla Hassan', 'layla.hassan@example.com', '0427849808', 'Can I update my adoption application after submitting it?', '2026-09-03 16:49:26', 0),
(14, 'Ben Sutton', 'ben.sutton@example.com', '0441241182', 'Do you have a newsletter I can sign up for?', '2026-09-04 16:49:26', 0),
(15, 'Chiara Rossi', 'chiara.rossi@example.com', '0444935348', 'Loved reading about Franklin the tortoise, any updates on him?', '2026-09-05 16:49:26', 0);

-- --------------------------------------------------------

--
-- Table structure for table `foster_carers`
--

CREATE TABLE `foster_carers` (
  `foster_carer_id` int(11) NOT NULL,
  `first_name` varchar(50) NOT NULL,
  `last_name` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `phone` varchar(20) NOT NULL,
  `suburb` varchar(100) NOT NULL,
  `preferred_animal_type` varchar(50) DEFAULT NULL,
  `capacity` int(11) NOT NULL DEFAULT 1,
  `status` enum('active','inactive') NOT NULL DEFAULT 'active',
  `notes` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `foster_carers`
--

INSERT INTO `foster_carers` (`foster_carer_id`, `first_name`, `last_name`, `email`, `phone`, `suburb`, `preferred_animal_type`, `capacity`, `status`, `notes`) VALUES
(1, 'Sarah', 'Nguyen', 'sarah.nguyen@example.com', '0410433218', 'Clayton', 'Dog', 2, 'active', 'Experienced with anxious rescues.'),
(2, 'James', 'O\'Brien', 'james.obrien@example.com', '0419600133', 'Glen Waverley', 'Cat', 3, 'active', NULL),
(3, 'Priya', 'Sharma', 'priya.sharma@example.com', '0489083863', 'Box Hill', 'Small mammals', 4, 'active', 'Runs a small setup for guinea pigs and hamsters.'),
(4, 'Liam', 'Chen', 'liam.chen@example.com', '0479402654', 'Dandenong', 'Dog', 1, 'inactive', 'Temporarily unavailable - renovating.'),
(5, 'Emily', 'Walker', 'emily.walker@example.com', '0423511615', 'Frankston', 'Cat', 2, 'active', NULL),
(6, 'Noah', 'Kelly', 'noah.kelly@example.com', '0459407816', 'Werribee', 'Dog', 2, 'active', 'Large yard, good with senior dogs.'),
(7, 'Ava', 'Singh', 'ava.singh@example.com', '0418495931', 'Sunshine', 'Bird', 5, 'active', NULL),
(8, 'Oliver', 'Brown', 'oliver.brown@example.com', '0403413164', 'Preston', 'Dog', 1, 'inactive', 'On extended leave.'),
(9, 'Chloe', 'Nguyen', 'chloe.nguyen2@example.com', '0475255341', 'Richmond', 'Cat', 3, 'active', NULL),
(10, 'Mason', 'Taylor', 'mason.taylor@example.com', '0492832764', 'Hawthorn', 'Rabbit', 2, 'active', NULL),
(11, 'Isla', 'Davies', 'isla.davies@example.com', '0483503056', 'Brunswick', 'Dog', 2, 'active', 'Prefers puppies.'),
(12, 'Ethan', 'Wilson', 'ethan.wilson@example.com', '0441395376', 'Coburg', 'Farm animals', 3, 'active', 'Has acreage, can take goats/sheep.'),
(13, 'Grace', 'Anderson', 'grace.anderson@example.com', '0472423884', 'Ringwood', 'Cat', 2, 'inactive', 'Moved interstate.'),
(14, 'Lucas', 'Martin', 'lucas.martin@example.com', '0496965328', 'Doncaster', 'Dog', 1, 'active', NULL),
(15, 'Mia', 'Thompson', 'mia.thompson@example.com', '0471012269', 'Cranbourne', 'Reptiles', 2, 'active', 'Comfortable housing tortoises and snakes.');

-- --------------------------------------------------------

--
-- Table structure for table `partner_organisations`
--

CREATE TABLE `partner_organisations` (
  `organisation_id` int(11) NOT NULL,
  `name` varchar(150) NOT NULL,
  `organisation_type` varchar(50) DEFAULT NULL,
  `contact_person` varchar(100) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `address` varchar(255) DEFAULT NULL,
  `website` varchar(255) DEFAULT NULL,
  `notes` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `partner_organisations`
--

INSERT INTO `partner_organisations` (`organisation_id`, `name`, `organisation_type`, `contact_person`, `email`, `phone`, `address`, `website`, `notes`) VALUES
(1, 'Clayton Family Vet', 'Veterinary Clinic', 'Dr. Alan Reid', 'contact@claytonvet.example.com', '0374016400', '12 Blackburn Rd, Clayton VIC 3168', 'https://claytonvet.example.com', 'Discounted desexing rates for rescue animals.'),
(2, 'Southeast Animal Hospital', 'Animal Hospital', 'Dr. Karen Lo', 'admin@seanimalhosp.example.com', '0352427868', '45 Princes Hwy, Dandenong VIC 3175', 'https://seanimalhosp.example.com', '24-hour emergency service.'),
(3, 'PetSupply Co', 'Pet Supply', 'Mark Ferris', 'sales@petsupplyco.example.com', '0301128059', '8 Market St, Box Hill VIC 3128', 'https://petsupplyco.example.com', 'Provides bulk food donations quarterly.'),
(4, 'Melbourne Groomers Guild', 'Grooming', 'Sandra Kim', 'hello@mgg.example.com', '0382620450', '3 High St, Preston VIC 3072', NULL, NULL),
(5, 'PawTrans Animal Transport', 'Transport', 'Dave Holt', 'bookings@pawtrans.example.com', '0353315869', '19 Nelson Pl, Werribee VIC 3030', 'https://pawtrans.example.com', 'Handles interstate foster transfers.'),
(6, 'Frankston Vet Surgery', 'Veterinary Clinic', 'Dr. Nina Patel', 'info@frankstonvet.example.com', '0323226025', '77 Beach St, Frankston VIC 3199', NULL, NULL),
(7, 'Companion Animal Boarding', 'Boarding', 'Tom Ellis', 'stay@companionboarding.example.com', '0363421607', '5 Ring Rd, Ringwood VIC 3134', 'https://companionboarding.example.com', 'Short-term boarding while fosters are arranged.'),
(8, 'Bark & Behaviour Training', 'Training', 'Julia Moss', 'julia@barkbehaviour.example.com', '0333754330', '22 Station St, Coburg VIC 3058', 'https://barkbehaviour.example.com', 'Offers reduced rates for adopted rescue dogs.'),
(9, 'Eastern Exotics Vet Clinic', 'Veterinary Clinic', 'Dr. Sam Osei', 'reception@easternexotics.example.com', '0336541458', '31 Warrigal Rd, Ashwood VIC 3147', NULL, 'Specialises in birds and reptiles.'),
(10, 'Community Pet Pantry', 'Pet Supply', 'Rachel Diaz', 'donations@petpantry.example.com', '0368501429', '14 Church St, Richmond VIC 3121', 'https://petpantry.example.com', NULL),
(11, 'Westside Animal Rescue Support', 'Community Partner', 'Greg Fallon', 'greg@westsiderescue.example.com', '0340196556', '9 Ballarat Rd, Sunshine VIC 3020', NULL, 'Cross-refers overflow intakes.'),
(12, 'Cranbourne Reptile Clinic', 'Veterinary Clinic', 'Dr. Wei Zhang', 'admin@cranbournereptile.example.com', '0398169340', '2 Sladen St, Cranbourne VIC 3977', NULL, NULL),
(13, 'Hawthorn Pet Insurance Partners', 'Insurance', 'Claire Bennett', 'partners@hawthorninsure.example.com', '0360883561', '61 Glenferrie Rd, Hawthorn VIC 3122', 'https://hawthorninsure.example.com', 'Offers discounted first-year premiums for adopters.'),
(14, 'Doncaster Farm & Pet Supplies', 'Pet Supply', 'Peter Lang', 'orders@doncasterfarmpet.example.com', '0359514846', '150 Doncaster Rd, Doncaster VIC 3108', NULL, 'Supplies feed for farm-animal fosters.'),
(15, 'Brunswick Community Vet Co-op', 'Veterinary Clinic', 'Dr. Fatima Ali', 'coop@brunswickvet.example.com', '0356482366', '88 Sydney Rd, Brunswick VIC 3056', 'https://brunswickvet.example.com', 'Not-for-profit, reduced-cost consults.');

-- --------------------------------------------------------

--
-- Table structure for table `species`
--

CREATE TABLE `species` (
  `species_id` int(11) NOT NULL,
  `species_name` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `species`
--

INSERT INTO `species` (`species_id`, `species_name`) VALUES
(8, 'Bird'),
(2, 'Cat'),
(9, 'Chicken'),
(1, 'Dog'),
(10, 'Duck'),
(7, 'Ferret'),
(11, 'Goat'),
(5, 'Guinea Pig'),
(6, 'Hamster'),
(13, 'Pig'),
(3, 'Rabbit'),
(12, 'Sheep'),
(15, 'Snake'),
(14, 'Tortoise'),
(4, 'Unknown');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `user_id` int(11) NOT NULL,
  `first_name` varchar(50) NOT NULL,
  `last_name` varchar(50) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `status` enum('active','inactive') NOT NULL DEFAULT 'active'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`user_id`, `first_name`, `last_name`, `username`, `password`, `status`) VALUES
(1, 'Emma', 'Wilson', 'emma_admin', '$2y$12$/U.uiozaJ2hgWOCuQiO92.xYLou.82VNo9tCedgsi8Yie6OlB4Q2C', 'active'),
(2, 'Tae', 'Kim', 'tae_admin', '$2y$12$/U.uiozaJ2hgWOCuQiO92.xYLou.82VNo9tCedgsi8Yie6OlB4Q2C', 'active'),
(3, 'Hannah', 'Lee', 'hannah_lee', '$2y$12$/U.uiozaJ2hgWOCuQiO92.xYLou.82VNo9tCedgsi8Yie6OlB4Q2C', 'active'),
(4, 'Ryan', 'Patel', 'ryan_patel', '$2y$12$/U.uiozaJ2hgWOCuQiO92.xYLou.82VNo9tCedgsi8Yie6OlB4Q2C', 'active'),
(5, 'Sophie', 'Turner', 'sophie_turner', '$2y$12$/U.uiozaJ2hgWOCuQiO92.xYLou.82VNo9tCedgsi8Yie6OlB4Q2C', 'active'),
(6, 'Jack', 'Robinson', 'jack_robinson', '$2y$12$/U.uiozaJ2hgWOCuQiO92.xYLou.82VNo9tCedgsi8Yie6OlB4Q2C', 'active'),
(7, 'Zoe', 'Campbell', 'zoe_campbell', '$2y$12$/U.uiozaJ2hgWOCuQiO92.xYLou.82VNo9tCedgsi8Yie6OlB4Q2C', 'active'),
(8, 'Daniel', 'Ng', 'daniel_ng', '$2y$12$/U.uiozaJ2hgWOCuQiO92.xYLou.82VNo9tCedgsi8Yie6OlB4Q2C', 'active'),
(9, 'Ella', 'Baker', 'ella_baker', '$2y$12$/U.uiozaJ2hgWOCuQiO92.xYLou.82VNo9tCedgsi8Yie6OlB4Q2C', 'inactive'),
(10, 'Nathan', 'Wood', 'nathan_wood', '$2y$12$/U.uiozaJ2hgWOCuQiO92.xYLou.82VNo9tCedgsi8Yie6OlB4Q2C', 'active'),
(11, 'Amelia', 'Scott', 'amelia_scott', '$2y$12$/U.uiozaJ2hgWOCuQiO92.xYLou.82VNo9tCedgsi8Yie6OlB4Q2C', 'active'),
(12, 'Ben', 'Murphy', 'ben_murphy', '$2y$12$/U.uiozaJ2hgWOCuQiO92.xYLou.82VNo9tCedgsi8Yie6OlB4Q2C', 'inactive'),
(13, 'Ivy', 'Cook', 'ivy_cook', '$2y$12$/U.uiozaJ2hgWOCuQiO92.xYLou.82VNo9tCedgsi8Yie6OlB4Q2C', 'active'),
(14, 'Leo', 'Ward', 'leo_ward', '$2y$12$/U.uiozaJ2hgWOCuQiO92.xYLou.82VNo9tCedgsi8Yie6OlB4Q2C', 'active'),
(15, 'Ruby', 'Foster', 'ruby_foster', '$2y$12$/U.uiozaJ2hgWOCuQiO92.xYLou.82VNo9tCedgsi8Yie6OlB4Q2C', 'active');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `adoption_applications`
--
ALTER TABLE `adoption_applications`
  ADD PRIMARY KEY (`application_id`),
  ADD KEY `animal_id` (`animal_id`);

--
-- Indexes for table `animals`
--
ALTER TABLE `animals`
  ADD PRIMARY KEY (`animal_id`),
  ADD KEY `breed_id` (`breed_id`),
  ADD KEY `foster_carer_id` (`foster_carer_id`);

--
-- Indexes for table `breeds`
--
ALTER TABLE `breeds`
  ADD PRIMARY KEY (`breed_id`),
  ADD UNIQUE KEY `uq_species_breed` (`species_id`,`breed_name`);

--
-- Indexes for table `contact_messages`
--
ALTER TABLE `contact_messages`
  ADD PRIMARY KEY (`message_id`);

--
-- Indexes for table `foster_carers`
--
ALTER TABLE `foster_carers`
  ADD PRIMARY KEY (`foster_carer_id`);

--
-- Indexes for table `partner_organisations`
--
ALTER TABLE `partner_organisations`
  ADD PRIMARY KEY (`organisation_id`);

--
-- Indexes for table `species`
--
ALTER TABLE `species`
  ADD PRIMARY KEY (`species_id`),
  ADD UNIQUE KEY `species_name` (`species_name`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`user_id`),
  ADD UNIQUE KEY `username` (`username`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `adoption_applications`
--
ALTER TABLE `adoption_applications`
  MODIFY `application_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT for table `animals`
--
ALTER TABLE `animals`
  MODIFY `animal_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=23;

--
-- AUTO_INCREMENT for table `breeds`
--
ALTER TABLE `breeds`
  MODIFY `breed_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=34;

--
-- AUTO_INCREMENT for table `contact_messages`
--
ALTER TABLE `contact_messages`
  MODIFY `message_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT for table `foster_carers`
--
ALTER TABLE `foster_carers`
  MODIFY `foster_carer_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT for table `partner_organisations`
--
ALTER TABLE `partner_organisations`
  MODIFY `organisation_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT for table `species`
--
ALTER TABLE `species`
  MODIFY `species_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `adoption_applications`
--
ALTER TABLE `adoption_applications`
  ADD CONSTRAINT `adoption_applications_ibfk_1` FOREIGN KEY (`animal_id`) REFERENCES `animals` (`animal_id`);

--
-- Constraints for table `animals`
--
ALTER TABLE `animals`
  ADD CONSTRAINT `animals_ibfk_1` FOREIGN KEY (`breed_id`) REFERENCES `breeds` (`breed_id`),
  ADD CONSTRAINT `animals_ibfk_2` FOREIGN KEY (`foster_carer_id`) REFERENCES `foster_carers` (`foster_carer_id`) ON DELETE SET NULL;

--
-- Constraints for table `breeds`
--
ALTER TABLE `breeds`
  ADD CONSTRAINT `breeds_ibfk_1` FOREIGN KEY (`species_id`) REFERENCES `species` (`species_id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
