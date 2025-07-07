-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: May 30, 2025 at 09:24 AM
-- Server version: 10.6.15-MariaDB
-- PHP Version: 8.0.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `toko_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `tb_admin`
--

CREATE TABLE `tb_admin` (
  `admin_id` int(11) NOT NULL,
  `username` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `nama` varchar(255) NOT NULL,
  `email` varchar(100) NOT NULL,
  `role` enum('admin','superadmin') NOT NULL DEFAULT 'admin',
  `status` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT NULL ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tb_admin`
--

INSERT INTO `tb_admin` (`admin_id`, `username`, `password`, `nama`, `email`, `role`, `status`, `created_at`, `updated_at`) VALUES
(1, 'admin', 'admin', 'admin', 'kelompok5@admin.com', 'superadmin', 1, '2025-03-10 13:53:02', '2025-03-10 14:02:14'),
(2, 'ghani', 'admin123', 'ghani', 'ghani@admin.com', 'admin', 1, '2025-03-10 13:53:02', '2025-03-10 14:01:50');

-- --------------------------------------------------------

--
-- Table structure for table `tb_barang`
--

CREATE TABLE `tb_barang` (
  `barang_id` int(11) NOT NULL,
  `merk_id` int(11) DEFAULT NULL,
  `kategori_id` int(11) DEFAULT NULL,
  `nama_barang` varchar(255) NOT NULL,
  `jenis_barang` text DEFAULT NULL,
  `gambar` varchar(255) NOT NULL,
  `harga_beli` decimal(10,2) DEFAULT NULL,
  `harga_jual` decimal(10,2) DEFAULT NULL,
  `stok` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tb_barang`
--

INSERT INTO `tb_barang` (`barang_id`, `merk_id`, `kategori_id`, `nama_barang`, `jenis_barang`, `gambar`, `harga_beli`, `harga_jual`, `stok`) VALUES
(1, 1, 1, 'ASUS ROG ZEPHYRUS G16 GA605WV-R946OL7G-OM RYZEN AI 9HX 370', 'Sistem Operasi\r\nWindows 11 Home\r\nCopilot+ PC experiences are coming. Requires free updates available starting late November 2024. Timing varies by device and region. See aka.ms/copilotpluspc\r\n\r\nProsesor\r\nAMD Ryzen™ AI 9 HX 370 Processor 2.0GHz (36MB Cache, up to 5.1GHz, 12 cores, 24 Threads); AMD XDNA™ NPU up to 50TOPS\r\n\r\nGrafis\r\nNVIDIA® GeForce RTX™ 4060 Laptop GPU (233 AI TOPs)\r\nROG Boost: 1940MHz* at 100W (1890MHz Boost Clock+50MHz OC, 85W+15W Dynamic Boost)\r\n8GB GDDR6\r\n\r\nNeural Processor\r\nAMD XDNA™ NPU up to 50TOPS\r\n\r\nTampilan\r\nROG Nebula Display\r\n16-inch\r\n2.5K (2560 x 1600, WQXGA) 16:10 aspect ratio\r\nOLED\r\nGlossy display\r\nDCI-P3: 100%\r\nRefresh Rate: 240Hz\r\nResponse Time: 0.2ms\r\nG-Sync / Adaptive-Sync\r\nPantone Validated\r\nMUX Switch + NVIDIA® Advanced Optimus\r\nSupport Dolby Vision HDR : Yes\r\n\r\nMemori\r\n16GB*2 LPDDR5X on board\r\nMax Capacity: 32GB\r\nSupport dual channel memory\r\n\r\nPenyimpanan\r\n1TB PCIe® 4.0 NVMe™ M.2 SSD\r\n\r\nExpansion Slots (includes used)\r\n2x M.2 PCIe\r\n\r\nPort I/O\r\n1x 3.5mm Combo Audio Jack\r\n1x HDMI 2.1 FRL\r\n2x USB 3.2 Gen 2 Type-A\r\n1x USB 3.2 Gen 2 Type-C support DisplayPort™ / power delivery\r\n1x Type-C USB 4 with support for DisplayPort™ / power delivery (data speed up to 40Gbps)\r\n1x card reader (SD Card) (SD Express 7.0)\r\n\r\nKeyboard dan Touchpad\r\nBacklit Chiclet Keyboard 1-Zone RGB\r\nTouchpad\r\nWith Copilot key\r\n*Copilot in Windows (in preview) is rolling out gradually within the latest update to Windows 11 in select global markets. Timing of availability varies by device and market. Learn more: https://www.microsoft.com/en-us/windows/copilot-ai-features?r=1#faq\r\n\r\nKamera\r\n1080P FHD IR Camera for Windows Hello\r\n\r\nAudio\r\nSmart Amp Technology\r\nDolby Atmos\r\nAI noise-canceling technology\r\nHi-Res certification (for headphone)\r\nBuilt-in 3-microphone array\r\n4-speaker (dual force woofer) system with Smart Amplifier Technology, 2 Tweeters\r\n\r\nJaringan dan Komunikasi\r\nWi-Fi 7(802.11be) (Triple band) 2*2+Bluetooth® 5.4 Wireless Card (*Bluetooth® version may change with OS version different.)\r\n\r\nBaterai\r\n90WHrs, 4S1P, 4-cell Li-ion\r\n\r\nSuplai Daya\r\nRectangle Conn, 200W AC Adapter, Output: 20V DC, 10A, 200W, Input: 100-240V AC, 50/60Hz universal\r\nTYPE-C, 100W AC Adapter, Output: 20V DC, 5A, 100W, Input: 100~240V AC 50/60Hz universal\r\n\r\nAURA SYNC\r\nYes\r\n\r\nLampu Perangkat\r\nSlash Lighting\r\n\r\nBerat\r\n1.85 Kg (4.08 lbs)\r\n\r\nDimensi (L x D x T)\r\n35.4 x 24.6 x 1.49 ~ 1.64 cm (13.94&quot; x 9.69&quot; x 0.59&quot; ~ 0.65&quot;)\r\n\r\nMicrosoft Office\r\nMicrosoft Office Home &amp; Student 2021 + Microsoft 365 Basic\r\n\r\nXbox Game Pass\r\nXbox Game Pass for PC_3 months (*Terms and exclusions apply. Offer only available in eligible markets for Xbox Game Pass for PC. Eligible markets are determined at activation. Game catalog varies by region, device, and time.)\r\n\r\nSecurity\r\nBIOS Administrator Password and User Password Protection\r\nTrusted Platform Module (Firmware TPM)\r\nPluton Security Processor\r\nSecured-core PC (Level 3)\r\nMcAfee® 30 days free trial\r\n\r\nDisertakan dalam Kotaknya\r\nROG Zephyrus G16 Sleeve (2024)\r\nROG Impact Gaming Mouse\r\nTYPE-C, 100W AC Adapter, Output: 20V DC, 5A, 100W, Input: 100~240V AC 50/60Hz universal', '1733467296.jpg', 25000000.00, 30000000.00, 1),
(2, 3, 1, 'HP PAVILION GAMING LAPTOP 15-EC2047AX RYZEN 5-5600H', '\r\nPart Number	443M2PA/AR6\r\nCPU	AMD Ryzen™ 5 5600H\r\nVGA	NVIDIA® GeForce® GTX 1650\r\nDisplay	15.6″ FHD (1920*1080), 144 Hz, IPS, micro-edge, anti-glare, 250 nits, 45% NTSC\r\nRAM	16GB DDR4\r\nStorage	512GB PCIe® NVMe™ M.2 SSD\r\nConnectivity	Realtek Wi-Fi CERTIFIED 6™ (2×2) and Bluetooth® 5.2 combo\r\nOperation System	Windows 10\r\nPorts	1x SuperSpeed USB Type-A 5Gbps signaling rate\r\n1x USB 2.0 Type-A (HP Sleep and Charge)\r\n1x HDMI 2.0; 1 RJ-45\r\n1x AC smart pin\r\n1x Headphone/microphone combo\r\nDimension	360 x 257 x 23,5 mm\r\nWeight	1980 gram\r\nWarranty	2 Years\r\nBundle	1. Office 2019 Home &amp; Student\r\n2. Backpack\r\nColor	Green', '1733467232.jpg', 9000000.00, 10000000.00, 2),
(3, 2, 1, 'LENOVO LAPTOP IDEAPAD GAMING 3-B7ID RYZEN 7-6800', 'Type : LENOVO IDEAPAD IP GAMING 3-15ARH7 B7ID GREY R7-6800\r\n\r\nProcessor : AMD Ryzen 7 6800H (8C / 16T, 3.2 / 4.7GHz, 4MB L2 / 16MB L3)\r\n\r\nType Grafis : NVIDIA GeForce RTX 3050 4GB GDDR6, Boost Clock 1500MHz, TGP 85W\r\n\r\nChipset : AMD SoC Platform\r\n\r\nPenyimpanan : 512GB SSD M.2 2242 PCIe 4.0x4 NVMe\r\n\r\nStorage Support\r\n\r\nUp to two drives, 2x M.2 SSD\r\n\r\nM.2 2280 SSD up to 1TB\r\n\r\nM.2 2242 SSD up to 1TB\r\n\r\nMemory RAM :8GB SO-DIMM DDR5-4800, dual-channel capable. Up to 16GB DDR5-4800 offering\r\n\r\nUkuran Layar : 15.6&quot; FHD (1920x1080) IPS 300nits Anti-glare, 165Hz, 100% sRGB, Free-Sync, DC dimmer\r\n\r\nKeyboard : 4-Zone RGB Backlit, English\r\n\r\nAudio Chip : High Definition (HD) Audio, Realtek ALC3287 codec\r\n\r\nSpeakers : Stereo speakers, 2W x2, Nahimic Audio\r\n\r\nCamera : HD 720p with Privacy Shutter\r\n\r\nMicrophone : 2x, Array\r\n\r\nEthernet : 100/1000M\r\n\r\nWLAN + Bluetooth : Wi-Fi 6 11ax, 2x2 + BT5.2\r\n\r\nStandard Ports:\r\n\r\n2x USB 3.2 Gen 1\r\n\r\n1x USB-C 3.2 Gen 2 (support data transfer, Power Delivery 3.0, and DisplayPort 1.4)\r\n\r\n1x HDMI 2.0\r\n\r\n1x Ethernet (RJ-45)\r\n\r\n1x Headphone / microphone combo jack (3.5mm)\r\n\r\n1x Power connector\r\n\r\nBattery : Integrated 45Wh\r\n\r\nPower Adapter : 170W Slim Tip (3-pin)\r\n\r\nWeight: Starting at 2.315 kg (5.1 lbs)\r\n\r\nCase Color : Glacier White\r\n\r\nOS : Windows 11 Home + Office Home and Student 2021\r\n\r\nWarranty : 2Y', '1733467131.jpg', 15990000.00, 16499999.00, 4),
(4, 1, 2, 'ASUS ZENBOOK S UX5304MA-OLEDS712', 'Tech Specs\r\nProduct Name	Zenbook S 13 OLED (UX5304) - UX5304MA-OLEDS712\r\nProcessor	Intel® Core™ Ultra 7 Processor 155U 1.7 GHz (12MB Cache, up to 4.8 GHz, 12 cores, 14 Threads) Intel® AI Boost NPU\r\nColor	Basalt Grey\r\nOperating System	Windows 11 Home\r\nDisplay	13.3-inch OLED 3K (2880 x 1800) OLED 16:10 aspect ratio glossy Pantone validated panel\r\nGraphics Card	Intel® Graphics\r\nMemory	1TB M.2 NVMe™ PCIe® 4.0 SSD\r\nStorage	LPDDR5X 32GB\r\nPorts	1x USB 3.2 Gen 2 Type-A 2x Thunderbolt™ 4 supports display / power delivery 1x HDMI 2.1 TMDS 1x 3.5mm Combo Audio Jack\r\nWeight	2.30\r\nPanel	13.3-inch\r\nColor	Basalt Grey\r\nDimensions (cm)	29.62 x 21.63 x 1.09 ~ 1.18 cm (11.66&quot; x 8.52&quot; x 0.43&quot; ~ 0.46&quot;)\r\nWarranty	2 Years Global Warranty', '1733467041.jpg', 10000000.00, 12599900.00, 5),
(6, 4, 2, 'Laptop Xiaomi Redmibook 15', 'Prosesor	Intel Core i3-1115G4 Dual Core up to 4.10 GHz\r\nVGA	Integrated Intel UHD Graphics\r\nRAM	8GB DDR4 3200 MHz\r\nStorage	SSD 256 GB/512 GB\r\nLayar	TN Panel 15.6 inci Full HD 1920 x 1080\r\nSpeaker	Stereo 2x 2W Audio DTS\r\nWebcam	HD 720p\r\nBaterai	46 Wh\r\nKeybo', '1733304650.jpg', 5260000.00, 6000000.00, 11),
(7, 5, 1, 'Axioo Pongo 725', '🌟 Spesifikasi Produk :\r\n✅ Processor: Intel Core I7 12650H (3.50GHz UPTO MAX 4.70GHz)\r\n✅ Ram : 16GB I 32GB I 64GB DDR4\r\n✅ Storage : 512GB I 1TB SSD M.2 2280 PCIe® NVMe®\r\n✅ Graphics : Nvidia Geforce RTX2050-4GB\r\n✅ Display : 15.6 Full HD IPS (1920 x 1080) re', '1733304391.jpg', 8000000.00, 10000000.00, 1),
(8, 1, 1, 'ASUS LAPTOP ROG STRIX-G G513IH-R765B6T', 'Processor Onboard : AMD Ryzen™ 7 4800H Processor 2.9 GHz (8M Cache, up to 4.2 GHz)\r\nMemori Standar : 8 GB DDR4 3200MHz\r\nTipe Grafis : NVIDIA® GeForce RTX™ 1650 Laptop GPU 4GB GDDR6 With ROG Boost up to 1615MHz at 50W (65W with Dynamic Boost)\r\nUkuran Layar', '1733465947.jpg', 10000000.00, 12000000.00, 10),
(11, 3, 3, 'HP LAPTOP 250-G8 [3V356PA] i3-1115G4', 'Processor Onboard : Intel® Core™ i3-1115G4 Processor (6MB Cache, up to 4.1 GHz)\r\nMemori Standar : 4 GB DDR4\r\nTipe Grafis : Intel® HD Graphics 620\r\nDisplay : 15,6″ diagonal HD SVA eDP anti-glare WLED-backlit, 220 cd/m², 67% sRGB (1366 x 768)\r\nAudio : 2 Int', '1733466132.jpg', 6000000.00, 7000000.00, 7),
(12, 2, 2, 'LENOVO THINKPAD E14 GEN6-5BID ULTRA 7', 'Processor Onboard : Intel® Core™ Ultra 7 155U, 12C (2P + 8E + 2LPE) / 14T, Max Turbo up to 4.8GHz, 12MB\r\nMemori Standar : 16GB SO-DIMM DDR5-5600\r\nTipe Grafis : Integrated Intel® Graphics\r\nUkuran Layar : 14″ WUXGA (1920×1200) IPS 300nits Anti-glare, 45% NT', '1733467373.jpg', 20999000.00, 22000000.00, 11),
(13, 10, 1, 'ACER GAMING LAPTOP NITRO AN515-57-921P i9-11900H', 'Processor : Intel® Core™ i9-11900H processor (24MB cache, up to 4.80Ghz)\r\nMemory : 16GB DDR4 3200Mhz\r\nStorage : 512GB SSD NVMe\r\nGraphics : NVIDIA® GeForce® RTX 3060 with 6GB of GDDR6\r\nDisplay : 15.6″ display with IPS (In-Plane Switching) technology, QHD 1', '1733467476.jpg', 20199000.00, 22000000.00, 3),
(14, 4, 3, 'Xiaomi Redmibook Intel i5-10210U', 'Merek: Xiaomi\r\nKlasifikasi: Ultrabook\r\nBahan penutup belakang: Semua Logam\r\nJenis baterai: Baterai Polimer Lithium-ion\r\n\r\nInti: Quad Core\r\nCPU: Intel Core i5-10210U 1.6GHz, up to 4.2GHz\r\nCPU Merek: Intel\r\nKecepatan Prosesor: 2.0GHz, Turbo 4.1GHz, Level 3 ', '1733539167.jpg', 10000000.00, 12000000.00, 10),
(15, 1, 1, 'ASUS LAPTOP ROG STRIX-G G513QC', 'Processor Onboard : AMD Ryzen™ 5 5600H Processor 3.3 GHz (16M Cache, up to 4.2 GHz)\r\nMemori Standar : 8 GB DDR4 3200MHz\r\nTipe Grafis : NVIDIA® GeForce RTX™ 3050 Laptop GPU 4GB GDDR6\r\nUkuran Layar : 15.6″ (16:9) LED-backlit FHD (1920×1080) Anti-Glare IPS-l', '1733539242.jpg', 15000000.00, 16000000.00, 11),
(16, 11, 2, 'APPLE MACBOOK AIR MGN63ID/A M1', 'Apple M1 chip with 8‑core CPU, 7‑core GPU, and 16‑core Neural Engine\r\n8GB unified memory\r\n256GB SSD storage\r\nRetina display with True Tone\r\nBacklit Magic Keyboard – US English\r\nTouch ID\r\nForce Touch trackpad\r\nTwo Thunderbolt / USB 4 ports', '1733629683.jpg', 12800000.00, 13000000.00, 10),
(17, 11, 2, 'APPLE MACBOOK PRO 14', 'Display: Liquid Retina XDR 14,2″ (3024 x 1964) 254 Pixel / Inc\r\nProcessor: 8-core CPU with 4 performance cores and 4 efficiency cores + 10-core GPU\r\n16-core Neural Engine 100GB/s memory bandwidth\r\nMemory: 16GB unified memory\r\nHard Disk: 1TB SSD\r\nSistem Op', '1733629827.jpg', 34999000.00, 36000000.00, 7),
(18, 11, 2, 'APPLE MACBOOK AIR MRYN3ID/A', 'Display: 15.3-inch (diagonal) LED-backlit display with IPS technology; 2560-by-1664 native resolution at 224 pixels per inch with support for millions of colors, 500 Nits\r\nProcessor: M3 Chip 8-core CPU with 4 perform­ance cores and 4 efficiency cores, 10-', '1733629892.jpg', 25499000.00, 26000000.00, 10),
(20, 1, 1, 'ASUS GAMING LAPTOP ROG ZEPHYRUS', 'Processor Onboard : AMD Ryzen™ 9 6900HS Mobile Processor (8-core/16-thread, 16MB cache, up to 4.9 GHz max boost)\r\nMemori Standar : 16GB DDR5 4800Mhz\r\nTipe Grafis : NVIDIA® GeForce RTX™ 3060 Notebook GPU 6GB GDDR6\r\nTGP : ROG Boost: 1475MHz* at 120W (1425MH', '1741590893.jpg', 20000000.00, 24999000.00, 9),
(21, 5, 2, 'NOTEBOOK AXIOO MYBOOK SAGA 10', 'Processor: Intel Core i3-1220P\r\nCode Name Intel Alder Lake\r\nLCD: 16&quot; inch 2,5K Resolution IPS Display (16:10)\r\nDisplay / Resolution: WQXGA 2560×1600\r\n\r\nMemory 8GB DDR4 (2x SODIMM DDR4 Up to 64GB dual channel, Frequency Up to 3200MHz)\r\nStorage 256 GB ', '1741591363.jpg', 6500000.00, 9000000.00, 15),
(22, 2, 3, 'Lenovo IdeaPad 3 14IAU7 82RJ00CNID Notebook', 'Spesifikasi :\r\nProcessor : Intel® Core™ i3-1215U, 6C (2P + 4E) / 8T, P-core 1.2 / 4.4GHz, E-core 0.9 / 3.3GHz, 10MB\r\nDisplay : 14″ FHD (1920×1080) TN 250nits Anti-glare\r\nMemory : 8GB Soldered DDR4-3200, Upgradable (Up to 16GB (8GB soldered + 8GB SO-DIMM))\r\nStorage : 512GB SSD M.2 2242 PCIe 4.0×4 NVMe, up to two drives, 1x 2.5″ HDD + 1x M.2 SSD\r\nGraphics : Intel® UHD Graphics\r\nKeyboard : Backlit, English\r\nWireless : Wi-Fi® 6, 11ax 2×2 + BT5.1\r\nPorts : 1x Card reader; 1x HDMI® 1.4b; 1x Headphone / microphone combo jack (3.5mm); 1x Power connector; 1x USB 2.0; 1x USB 3.2 Gen 1; 1x USB-C® 3.2 Gen 1 (support data transfer, Power Delivery and DisplayPort™ 1.2)\r\nCamera : HD 720p with Privacy Shutter\r\nAudio : Stereo speakers, 1.5W x2, Dolby® Audio™\r\nBattery : Integrated 38Wh, 65W Round Tip (3-pin)\r\nFree : Bag\r\nOS : Windows 11 Home + Office Home and Student 2021\r\nGaransi Resmi Lenovo 2Y Premium Care', '1741591709.jpg', 5000000.00, 8000000.00, 13),
(23, 10, 2, 'Nitro V 16 (ANV16-41-R7W0) | AMD R7 RTX4050', 'Highlights :\r\n• Performa visualisasi, movement object yang tinggi &amp; proses editing yang cepat dengan AMD Ryzen 8000 series.\r\n• Performa grafis memukau dan pengalaman bermain game lebih real tanpa lag dengan Maximum Graphic Performance (MGP) tertinggi dikelasnya dengan RTX 4050.\r\n• Performa lebih stabil dan lebih tahan lama, didukung oleh kipas ganda terbaik dikelasnya.\r\n• 8x lebih cepat dalam transfer data dengan USB4 dibandingkan USB 3.0.\r\n• 1.4x lebih cepat dengan memory DDR5 dikelasnya.\r\n\r\nNitro V 16 (ANV16-41-R7W0)\r\n• Processor : AMD Ryzen™ 7 8845HS (8Mb Cache, up to 5.10Ghz) processor\r\n• OS : Windows 11 Home\r\n• Memory : 2*8GB DDR5\r\n• Storage : 512GB SSD NVMe\r\n• Inch, Res, Ratio, Panel : 16&quot; WUXGA (500nits) 165Hz, 100% sRGB\r\n• Graphics : NVIDIA® GeForce® RTX 4050 with 6GB of GDDR6', '1741592059.jpg', 15000000.00, 18999999.00, 6),
(25, 4, 3, 'RedmiBook Pro 14 MX450 Laptop Intel I5-11320H 16GB 512GB SSD Komputer Notebook Versi Global Win10 PC', 'Product details:\r\n\r\n\r\n\r\nLayar FullView tak terbatas 2.5K\r\n\r\nUntuk pertama kalinya, bingkai logam di sekitar layar hilang. Bersama dengan rasio layar-ke-bodi 88.2% yang luar biasa, visi Anda didorong melampaui batas. Layar 2.5K dengan gamut warna 100% sRGB memungkinkan detail yang tajam dan dinamis. Dan rasio aspek 16:10 sempurna untuk membaca atau menulis.\r\n\r\n\r\n\r\nBegitu ringan, begitu tepat 1.46kg ultra-ringan\r\n\r\nBagaimana orang bisa mengatakan tidak pada salah satu buku catatan yang paling ringan? Beratnya hanya di bawah 1.46 kg, hal terakhir yang perlu anda khawatirkan adalah membawa beban berat di sekitar atas nama portabilitas. Rasakan keanggunan lapisan sandblasted anodisa yang dipadukan dengan buku catatan yang sangat ringan.\r\n\r\n\r\n\r\nKekuatan mutlak Intel® Core generasi 11™I5-1135G7/i7-1165G7\r\n\r\nKami membawa kepada Anda Intel generasi 11 Intel prosesor inti, pembangkit tenaga listrik produktivitas. Kecepatan jam yang lebih tinggi dan kinerja multi-core yang lebih baik untuk multitasking, foto yang lebih baik, pengeditan video, dan banyak lagi!\r\n\r\n\r\n\r\nJangan memainkan permainan menunggu Grafik NVIDIA® GeForce® MX450 / Intel® Iris® Xe\r\n\r\nMemberi Anda kinerja game yang tak tertandingi dan pengalaman mengedit foto dan video yang lebih baik.\r\n\r\n\r\n\r\nPlay it Cool Dilengkapi dengan ventilasi pendingin yang lebih besar\r\n\r\nDilengkapi dengan kipas berdiameter besar 6mm yang membawa pendinginan yang sangat baik ke seluruh mesin. Ini membuat mesin Anda tetap dingin sehingga Anda dapat memegang Anda.\r\n\r\n\r\n\r\nKeamanan setiap detail Dirancang untuk melindungi privasi Anda.\r\n\r\nTombol daya juga dapat mendeteksi sidik jari Anda untuk memungkinkan Anda mengakses desktop dengan cepat dan aman hanya dengan satu sentuhan.\r\n\r\n\r\n\r\nSpecifications:\r\n\r\nVideo Memory Capacity: 2GB\r\n\r\nDisplay Size: 14&quot;\r\n\r\nDisplay Ratio: 16:10\r\n\r\nType: Slim Laptop\r\n\r\nDimensions (WxHxD): 315.6mmx220.4mmx17.25mm\r\n\r\nOperating System: Windows 10\r\n\r\nGraphics Card Model: NVIDIA Ge\r\n\r\nProcessor: i5-11320HForce MX450\r\n\r\nHard Drive Type: SSD\r\n\r\nScreen Refresh Rate: 60Hz\r\n\r\nHard Drive Capacity: 512GB\r\n\r\nWeight (Battery Included): &lt;1.5Kg\r\n\r\nModel Number: RedmiBook Pro 14(Enhanced Edition)\r\n\r\nCPU Brand/Model: Intel i5-11300H\r\n\r\nPanel Type: IPS\r\n\r\nRAM: 16GB\r\n\r\nBody Material: Metal\r\n\r\nThickness: 15mm- 18mm\r\n\r\nFeature: Camera\r\n\r\nBacklit keyboard\r\n\r\nFingerprint Recognition\r\n\r\nOptical Drive Type: None\r\n\r\nAverage Battery Life(in hours): About 11.5 hours of local video play\r\n\r\n\r\n\r\nDi dalam kotak\r\n\r\nPC x 1\r\n\r\nAdaptor Tipe C x 1\r\n\r\nKabel adaptor (USB-C) x 1\r\n\r\nQSG x 1', '1741592498.png', 10000000.00, 15000000.00, 9);

-- --------------------------------------------------------

--
-- Table structure for table `tb_custom_orders`
--

CREATE TABLE `tb_custom_orders` (
  `order_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `ram` varchar(50) NOT NULL,
  `storage` varchar(100) NOT NULL,
  `processor` varchar(100) NOT NULL,
  `vga` varchar(100) NOT NULL,
  `screen_size` varchar(50) NOT NULL,
  `screen_type` varchar(100) NOT NULL,
  `operating_system` varchar(100) NOT NULL,
  `additional_specs` text DEFAULT NULL,
  `budget` int(11) NOT NULL,
  `down_payment` int(12) NOT NULL,
  `status` enum('pending','processing','completed','cancelled') NOT NULL DEFAULT 'pending',
  `admin_notes` text DEFAULT NULL,
  `admin_id` int(11) DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT NULL ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tb_custom_orders`
--

INSERT INTO `tb_custom_orders` (`order_id`, `user_id`, `ram`, `storage`, `processor`, `vga`, `screen_size`, `screen_type`, `operating_system`, `additional_specs`, `budget`, `down_payment`, `status`, `admin_notes`, `admin_id`, `created_at`, `updated_at`) VALUES
(1, 14, '16GB', 'HDD 256GB', 'Intel Intel Core i5', 'NVIDIA GeForce RTX 3070', '15.6', 'LCD', 'Windows 11 Home', 'Untuk Gaming dan Kerja Kantoran', 30000000, 0, 'completed', 'oke', 1, '2025-03-11 13:53:16', '2025-03-12 11:04:33'),
(2, 5, '32GB', 'SSD + HDD 1TB', 'AMD AMD Ryzen 7', 'AMD Radeon RX 7700', '15.6', 'OLED', 'Windows 11 Pro', 'untuk bermain anak saya ', 50000000, 0, 'completed', 'tes', 1, '2025-03-12 14:30:10', '2025-03-12 14:45:31'),
(3, 5, '32GB', 'SSD + HDD 4TB', 'AMD AMD Ryzen 9', 'NVIDIA GeForce RTX 3060', '15.6', 'LCD', 'Windows 11 Pro', 'Buat Nonton pornno sama istri', 35000000, 0, 'cancelled', 'akan saya carikan', 1, '2025-03-17 17:54:11', '2025-03-17 17:56:06'),
(4, 16, '16GB', 'HDD 512GB', 'Intel Intel Core i9', 'NVIDIA GeForce RTX 3080', '15.6', 'OLED', 'Windows 11 Pro', 'buat lihat mu vs bilbao', 40000000, 0, 'completed', 'ya saya akan carikan\r\n', 1, '2025-05-09 16:44:33', '2025-05-09 16:47:32'),
(5, 5, '8GB', 'NVMe SSD 512GB', 'Intel Intel Core i7', 'NVIDIA GeForce RTX 3080', '15.6', 'OLED', 'Linux', 'untuk server lokal dirumah saya', 45000000, 0, 'completed', 'okee akan saya carikan\r\n', 1, '2025-05-19 19:09:15', '2025-05-19 19:12:27'),
(6, 17, '32GB', 'NVMe SSD 4TB', 'Intel Intel Core i5', 'NVIDIA GeForce RTX 3070', '17.3', 'OLED', 'Linux', 'server lokal perusahaan', 100000000, 0, 'completed', 'ok saya proses\r\n', 1, '2025-05-22 13:15:47', '2025-05-22 13:36:44'),
(7, 17, '8GB', 'SSD 256GB', 'Intel Intel Core i5', 'NVIDIA GeForce GTX 1660', '17.3', 'OLED', 'Linux', '0', 20000000, 6000000, 'pending', NULL, NULL, '2025-05-22 16:51:21', NULL),
(8, 17, '16GB', 'NVMe SSD 512GB', 'Intel Intel Core i5', 'NVIDIA GeForce RTX 3080', '15.6', 'LCD', 'Linux', '0', 30000000, 9000000, 'pending', NULL, NULL, '2025-05-22 16:52:49', NULL),
(9, 17, '16GB', 'SSD + HDD 4TB', 'AMD AMD Ryzen 5', 'NVIDIA GeForce RTX 3060', '15.6', 'OLED', 'Linux', 'tes123', 20000000, 6000000, 'completed', 'acc\r\n', 1, '2025-05-22 16:55:58', '2025-05-22 16:57:33'),
(10, 5, '8GB', 'HDD 4TB', 'Intel Intel Core i5', 'NVIDIA GeForce GTX 1650', '14', 'OLED', 'Linux', 'tes', 10000000, 3000000, 'pending', NULL, NULL, '2025-05-23 17:21:02', NULL),
(11, 5, '8GB', 'NVMe SSD 1TB', 'AMD AMD Ryzen 5', 'NVIDIA GeForce RTX 4090', '15.6', 'OLED', 'Without OS', 'tes', 40000000, 12000000, 'pending', NULL, NULL, '2025-05-26 13:54:30', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `tb_detail_pembelian`
--

CREATE TABLE `tb_detail_pembelian` (
  `barang_id` int(11) NOT NULL,
  `id_pembelian` int(11) NOT NULL,
  `jumlah` int(10) DEFAULT NULL,
  `subtotal` decimal(10,2) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tb_detail_pembelian`
--

INSERT INTO `tb_detail_pembelian` (`barang_id`, `id_pembelian`, `jumlah`, `subtotal`) VALUES
(1, 1, 1, 10000000.00),
(1, 2, 2, 20000000.00),
(1, 3, 4, 40000000.00),
(1, 5, 2, 20000000.00),
(1, 8, 1, 30000000.00),
(1, 9, 1, 30000000.00),
(1, 12, 2, 60000000.00),
(1, 18, 1, 30000000.00),
(1, 23, 1, 30000000.00),
(1, 24, 1, 30000000.00),
(1, 26, 1, 30000000.00),
(1, 29, 1, 30000000.00),
(1, 30, 1, 30000000.00),
(1, 36, 1, 30000000.00),
(2, 7, 2, 20000000.00),
(2, 11, 1, 10000000.00),
(2, 18, 2, 20000000.00),
(2, 28, 1, 10000000.00),
(2, 31, 1, 10000000.00),
(2, 32, 1, 10000000.00),
(2, 41, 1, 10000000.00),
(2, 45, 1, 10000000.00),
(3, 4, 3, 36000000.00),
(3, 12, 1, 16499999.00),
(3, 15, 1, 16499999.00),
(3, 18, 1, 16499999.00),
(3, 33, 1, 16499999.00),
(3, 51, 1, 16499999.00),
(4, 19, 2, 25199800.00),
(4, 20, 1, 12599900.00),
(4, 21, 1, 12599900.00),
(4, 22, 1, 12599900.00),
(4, 26, 1, 12599900.00),
(4, 35, 1, 12599900.00),
(6, 10, 1, 6000000.00),
(7, 16, 1, 10000000.00),
(7, 17, 2, 20000000.00),
(7, 26, 1, 10000000.00),
(8, 6, 1, 12000000.00),
(8, 26, 1, 12000000.00),
(8, 40, 1, 12000000.00),
(11, 10, 1, 7000000.00),
(11, 61, 1, 7000000.00),
(11, 65, 1, 7000000.00),
(12, 18, 1, 22000000.00),
(13, 47, 1, 22000000.00),
(13, 52, 1, 22000000.00),
(13, 54, 1, 22000000.00),
(13, 57, 1, 22000000.00),
(13, 58, 1, 22000000.00),
(13, 60, 1, 22000000.00),
(13, 63, 1, 22000000.00),
(13, 68, 1, 22000000.00),
(13, 70, 1, 22000000.00),
(14, 37, 1, 12000000.00),
(14, 62, 1, 12000000.00),
(15, 64, 1, 16000000.00),
(16, 42, 1, 13000000.00),
(16, 67, 1, 13000000.00),
(17, 13, 1, 36000000.00),
(17, 14, 1, 36000000.00),
(17, 45, 1, 36000000.00),
(17, 53, 1, 36000000.00),
(17, 55, 1, 36000000.00),
(18, 43, 1, 26000000.00),
(18, 69, 1, 26000000.00),
(20, 45, 1, 24999000.00),
(22, 46, 1, 8000000.00),
(22, 54, 1, 8000000.00),
(23, 53, 1, 18999999.00),
(25, 46, 1, 15000000.00);

-- --------------------------------------------------------

--
-- Table structure for table `tb_detail_penjualan`
--

CREATE TABLE `tb_detail_penjualan` (
  `penjualan_id` int(11) DEFAULT NULL,
  `barang_id` int(11) DEFAULT NULL,
  `id` int(11) NOT NULL,
  `jumlah` int(11) DEFAULT NULL,
  `subtotal` decimal(10,2) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tb_detail_penjualan`
--

INSERT INTO `tb_detail_penjualan` (`penjualan_id`, `barang_id`, `id`, `jumlah`, `subtotal`) VALUES
(1, 1, 1, 2, 20000000.00),
(2, 1, 2, 4, 40000000.00),
(3, 3, 3, 3, 36000000.00),
(4, 1, 4, 2, 20000000.00),
(5, 8, 5, 1, 12000000.00),
(6, 2, 6, 2, 20000000.00),
(7, 1, 7, 1, 30000000.00),
(8, 1, 8, 1, 30000000.00),
(9, 6, 9, 1, 6000000.00),
(9, 11, 10, 1, 7000000.00),
(10, 2, 11, 1, 10000000.00),
(11, 1, 12, 2, 60000000.00),
(11, 3, 13, 1, 16499999.00),
(12, 17, 14, 1, 36000000.00),
(13, 17, 15, 1, 36000000.00),
(14, 3, 16, 1, 16499999.00),
(15, 7, 17, 1, 10000000.00),
(16, 7, 18, 2, 20000000.00),
(17, 2, 19, 2, 20000000.00),
(17, 1, 20, 1, 30000000.00),
(17, 3, 21, 1, 16499999.00),
(17, 12, 22, 1, 22000000.00),
(18, 4, 23, 2, 25199800.00),
(19, 4, 24, 1, 12599900.00),
(20, 4, 25, 1, 12599900.00),
(21, 4, 26, 1, 12599900.00),
(22, 1, 27, 1, 30000000.00),
(23, 1, 28, 1, 30000000.00),
(24, 1, 29, 1, 30000000.00),
(24, 4, 30, 1, 12599900.00),
(24, 7, 31, 1, 10000000.00),
(24, 8, 32, 1, 12000000.00),
(25, 2, 33, 1, 10000000.00),
(26, 1, 34, 1, 30000000.00),
(27, 1, 35, 1, 30000000.00),
(28, 2, 36, 1, 10000000.00),
(29, 2, 37, 1, 10000000.00),
(30, 3, 38, 1, 16499999.00),
(31, 4, 39, 1, 12599900.00),
(32, 1, 40, 1, 30000000.00),
(33, 14, 41, 1, 12000000.00),
(34, 8, 42, 1, 12000000.00),
(35, 2, 43, 1, 10000000.00),
(36, 16, 44, 1, 13000000.00),
(37, 18, 45, 1, 26000000.00),
(38, 2, 46, 1, 10000000.00),
(38, 17, 47, 1, 36000000.00),
(38, 20, 48, 1, 24999000.00),
(39, 22, 49, 1, 8000000.00),
(39, 25, 50, 1, 15000000.00),
(40, 13, 51, 1, 22000000.00),
(43, 3, 54, 1, 16499999.00),
(44, 13, 55, 1, 22000000.00),
(45, 17, 56, 1, 36000000.00),
(45, 23, 57, 1, 18999999.00),
(46, 13, 58, 1, 22000000.00),
(46, 22, 59, 1, 8000000.00),
(47, 17, 60, 1, 36000000.00),
(48, 13, 61, 1, 22000000.00),
(49, 13, 62, 1, 22000000.00),
(50, 13, 63, 1, 22000000.00),
(51, 11, 64, 1, 7000000.00),
(52, 14, 65, 1, 12000000.00),
(53, 13, 66, 1, 22000000.00),
(54, 15, 67, 1, 16000000.00),
(55, 11, 68, 1, 7000000.00),
(56, 16, 69, 1, 13000000.00),
(57, 13, 70, 1, 22000000.00),
(58, 18, 71, 1, 26000000.00),
(59, 13, 72, 1, 22000000.00);

-- --------------------------------------------------------

--
-- Table structure for table `tb_kategori`
--

CREATE TABLE `tb_kategori` (
  `kategori_id` int(11) NOT NULL,
  `nama_kategori` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tb_kategori`
--

INSERT INTO `tb_kategori` (`kategori_id`, `nama_kategori`) VALUES
(1, 'laptop gaming'),
(2, 'Laptop Kantor'),
(3, 'Laptop Sekolah'),
(9, 'Laptop unesa');

-- --------------------------------------------------------

--
-- Table structure for table `tb_merk`
--

CREATE TABLE `tb_merk` (
  `merk_id` int(11) NOT NULL,
  `nama_merk` varchar(255) NOT NULL,
  `deskripsi` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tb_merk`
--

INSERT INTO `tb_merk` (`merk_id`, `nama_merk`, `deskripsi`) VALUES
(1, 'Asus', 'merk asus'),
(2, 'Lenovo', 'lenovo anjay'),
(3, 'HP', 'HP laptop'),
(4, 'Xiaomi', 'Laptop China'),
(5, 'Axio', 'Laptop baru'),
(10, 'Acer', 'Laptop Acer'),
(11, 'Macbook', 'Apple Laptop Macbook'),
(15, 'Unesa', 'Merk Laptop unesa\r\n');

-- --------------------------------------------------------

--
-- Table structure for table `tb_pembayaran`
--

CREATE TABLE `tb_pembayaran` (
  `pembayaran_id` int(11) NOT NULL,
  `jenis_pembayaran` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tb_pembayaran`
--

INSERT INTO `tb_pembayaran` (`pembayaran_id`, `jenis_pembayaran`) VALUES
(1, 'BCA'),
(2, 'BRI'),
(3, 'BNI'),
(4, 'BTN'),
(5, 'MANDIRI'),
(6, 'BSI'),
(7, 'DANAMON'),
(8, 'CIMB NIAGA');

-- --------------------------------------------------------

--
-- Table structure for table `tb_pembelian`
--

CREATE TABLE `tb_pembelian` (
  `id_pembelian` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `pembayaran_id` int(11) DEFAULT NULL,
  `tanggal` date NOT NULL,
  `bayar` decimal(10,2) DEFAULT NULL,
  `jumlah_pembayaran` decimal(10,2) DEFAULT NULL,
  `kembalian` decimal(10,2) DEFAULT NULL,
  `penjualan_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tb_pembelian`
--

INSERT INTO `tb_pembelian` (`id_pembelian`, `user_id`, `pembayaran_id`, `tanggal`, `bayar`, `jumlah_pembayaran`, `kembalian`, `penjualan_id`) VALUES
(1, 5, 1, '2025-05-26', 22000000.00, 22000000.00, 0.00, 2);

-- --------------------------------------------------------

--
-- Table structure for table `tb_penjualan`
--

CREATE TABLE `tb_penjualan` (
  `penjualan_id` int(11) NOT NULL,
  `admin_id` int(11) DEFAULT NULL,
  `tanggal` timestamp NOT NULL DEFAULT current_timestamp(),
  `bayar` decimal(10,2) DEFAULT NULL,
  `total` decimal(10,2) DEFAULT NULL,
  `kembalian` decimal(10,2) DEFAULT NULL,
  `id_pembelian` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tb_penjualan`
--

INSERT INTO `tb_penjualan` (`penjualan_id`, `admin_id`, `tanggal`, `bayar`, `total`, `kembalian`, `id_pembelian`) VALUES
(1, 1, '2025-05-26 01:50:12', 22000000.00, 22000000.00, 0.00, 2);

-- --------------------------------------------------------

--
-- Table structure for table `tb_supplier`
--

CREATE TABLE `tb_supplier` (
  `supplier_id` int(11) NOT NULL,
  `barang_id` int(11) DEFAULT NULL,
  `nama` varchar(255) NOT NULL,
  `alamat` varchar(255) DEFAULT NULL,
  `telepon` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tb_supplier`
--

INSERT INTO `tb_supplier` (`supplier_id`, `barang_id`, `nama`, `alamat`, `telepon`) VALUES
(1, 1, 'dony', 'nganjoek pusat', '0867653752671'),
(2, 2, 'dapa', 'nganjoek pusat', '087657472747'),
(3, 3, 'cimok', 'krian', '081820820808'),
(4, 16, 'zelda', 'tuban poesat of city', '08578658578'),
(5, 14, 'titi', 'Sidoarjo', '0812376484302'),
(6, 1, 'alipa', 'Taman', '081289362410'),
(7, 7, 'bilaa', 'Simo', '081976247308'),
(8, 13, 'mojo', 'tarik', '08526387649'),
(9, 1, 'abdi', 'jember', '085283651037'),
(10, 2, 'rusdi', 'ngawi city', '0854326193527'),
(11, 18, 'azril', 'ngawi barat', '089734263825'),
(12, 6, 'dhani', 'sana', '081848484545'),
(13, 7, 'dony ganteng', 'sini', '085454547565');

-- --------------------------------------------------------

--
-- Table structure for table `tb_user`
--

CREATE TABLE `tb_user` (
  `user_id` int(11) NOT NULL,
  `nama` varchar(255) NOT NULL,
  `nama_lengkap` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `alamat` longtext DEFAULT NULL,
  `telepon` varchar(15) DEFAULT NULL,
  `photo` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tb_user`
--

INSERT INTO `tb_user` (`user_id`, `nama`, `nama_lengkap`, `password`, `alamat`, `telepon`, `photo`) VALUES
(1, 'mojo', '', '', 'sini', '0909090', NULL),
(2, 'dhani', '', 'akudhani', 'sini', '09090909', NULL),
(3, 'dhani', '', '$2a$12$QudVxwGfY7.D99OAMDFq5.m6YjVktTELHyWIlAV90wt9jbc3KBM1S', 'sini', '12122121', NULL),
(4, 'mojo', '', '$2y$10$cRc8EhVH0M7RLOD//h1NWe2tBgUKaaSZ2IYF8DR1d/y0jupUnMjv6', 'sini', '89898989', NULL),
(5, 'firman', 'FIRMANDHANI SETYO T', '$2y$10$lGmD.SjjrNrgnLLa2R7.wuViiNbHo/M4VzyNZKnpf/gJiKpv5fkYO', 'disini senang disana senang', '085784777172', 'uploads/profile_photos/user_5_1741779871.jpg'),
(6, 'cimok', '', '$2y$10$dtsqNEVyAfbLnUKIcYuNJuqqvKr1yq378AbdsqA6m2Mk6fNlAmM6q', 'krian', '08998983983', NULL),
(7, 'tes1', '', '$2y$10$64DCMcoZtOobtCn/qjgB5ubzkeCuUjhoaXJuM7iSbaaffflFRx79G', 'sanaa', '0897798788', NULL),
(8, 'tes2', '', '$2y$10$YppCU4A2raE14YderKB3UOzZkIfIP2BYUahnjrEq5DqrlCKpxR.cG', 'sinisiansinaisnsain', '0823232323232', NULL),
(9, 'tes5', '', '$2y$10$.qLvg6cRDow6r/srtHUxCufBlEVqfCo27wEWhUoDZ6w1Azu6Kv3ay', 'sini sana', '0876276327362', NULL),
(10, 'tes4', '', '$2y$10$OEkSO2vAjQdFyUId3468I.FXG1mo7gVZVzvAn6QMDxuaX9i4yDR/S', 'sini', '085755654486', NULL),
(11, 'daffa', '', '$2y$10$I1wf6AeqwkuMePehyvGwiuO2iA.cB48VECkekFIOhjzpOUJr0.Vji', 'nganjuk pusat', '0874525698', NULL),
(12, 'coba1', '', '$2y$10$brdC07kRNVz39L2xQyt/DOnYEVFsO5fxl1tYMVrkJhAzFGr9T5Mga', 'sini looo', '0875485745587', NULL),
(13, 'cheysa', '', '$2y$10$mXi3Egqr8BxZ7/Zy7cFh8OXxuUvc1es2k68W7BUtVQUI2Lu/FF4Ei', 'simo', '08545665465', NULL),
(14, 'cimcim', '', '$2y$10$ewWuJ2crP9or7Kmfxj7ACOCYQnBgN0v63bVg6gEQXpDMjfnTanBB.', 'akudisini', '08578477874', 'uploads/profile_photos/user_14_1741593986.png'),
(15, 'tes12', '', '$2y$10$adtaOspugpKriHq0YdM7qOVUe8zgib8WCKtYNVZibKifxmDOmXWHK', 'akuuu disini', '08541756964', NULL),
(16, 'waringin', '', '$2y$10$RuTQvIooGwA8W/uHwmQKO.17EVXQOBeVTYnDmVgVd9/qCnuPY2x0y', 'waringin IT', '085874555464', NULL),
(17, 'rakha', 'Rakha Adyatma', '$2y$10$Ak6G6tZdscCOZFRZbhcw.uKXPHSpeLj3adZDsoWYjXMYD526u9Eeq', 'A10 Ketintang', '08765432123', NULL),
(18, 'cheysaku', '', '$2y$10$8iB6c6QGwY7sGZQazF6KbuTXQSalHoc00fvgKUMXpWnhsTJF3qWO2', 'simongagrok', '085624896554', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `tb_wishlist`
--

CREATE TABLE `tb_wishlist` (
  `wishlist_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `barang_id` int(11) NOT NULL,
  `tanggal` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tb_wishlist`
--

INSERT INTO `tb_wishlist` (`wishlist_id`, `user_id`, `barang_id`, `tanggal`) VALUES
(17, 14, 1, '2025-03-10 19:36:12'),
(18, 5, 7, '2025-03-12 12:29:26'),
(19, 5, 14, '2025-03-12 12:29:27'),
(20, 5, 15, '2025-03-12 12:29:28'),
(21, 5, 2, '2025-03-16 14:55:18'),
(22, 16, 1, '2025-05-09 11:39:30'),
(23, 16, 2, '2025-05-09 11:39:33'),
(24, 17, 3, '2025-05-22 08:20:52'),
(25, 17, 2, '2025-05-22 08:20:53'),
(26, 5, 13, '2025-05-24 17:48:34'),
(27, 5, 16, '2025-05-24 17:48:36'),
(28, 5, 18, '2025-05-24 17:48:37');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `tb_admin`
--
ALTER TABLE `tb_admin`
  ADD PRIMARY KEY (`admin_id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `tb_barang`
--
ALTER TABLE `tb_barang`
  ADD PRIMARY KEY (`barang_id`),
  ADD KEY `merk_id` (`merk_id`),
  ADD KEY `kategori_id` (`kategori_id`);

--
-- Indexes for table `tb_custom_orders`
--
ALTER TABLE `tb_custom_orders`
  ADD PRIMARY KEY (`order_id`);

--
-- Indexes for table `tb_detail_pembelian`
--
ALTER TABLE `tb_detail_pembelian`
  ADD PRIMARY KEY (`barang_id`,`id_pembelian`),
  ADD KEY `id_pembelian` (`id_pembelian`);

--
-- Indexes for table `tb_detail_penjualan`
--
ALTER TABLE `tb_detail_penjualan`
  ADD PRIMARY KEY (`id`),
  ADD KEY `penjualan_id` (`penjualan_id`),
  ADD KEY `barang_id` (`barang_id`);

--
-- Indexes for table `tb_kategori`
--
ALTER TABLE `tb_kategori`
  ADD PRIMARY KEY (`kategori_id`);

--
-- Indexes for table `tb_merk`
--
ALTER TABLE `tb_merk`
  ADD PRIMARY KEY (`merk_id`);

--
-- Indexes for table `tb_pembayaran`
--
ALTER TABLE `tb_pembayaran`
  ADD PRIMARY KEY (`pembayaran_id`);

--
-- Indexes for table `tb_pembelian`
--
ALTER TABLE `tb_pembelian`
  ADD PRIMARY KEY (`id_pembelian`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `pembayaran_id` (`pembayaran_id`),
  ADD KEY `idx_pembelian_penjualan_id` (`penjualan_id`);

--
-- Indexes for table `tb_penjualan`
--
ALTER TABLE `tb_penjualan`
  ADD PRIMARY KEY (`penjualan_id`),
  ADD KEY `admin_id` (`admin_id`),
  ADD KEY `idx_penjualan_id_pembelian` (`id_pembelian`);

--
-- Indexes for table `tb_supplier`
--
ALTER TABLE `tb_supplier`
  ADD PRIMARY KEY (`supplier_id`),
  ADD KEY `barang_id` (`barang_id`);

--
-- Indexes for table `tb_user`
--
ALTER TABLE `tb_user`
  ADD PRIMARY KEY (`user_id`);

--
-- Indexes for table `tb_wishlist`
--
ALTER TABLE `tb_wishlist`
  ADD PRIMARY KEY (`wishlist_id`),
  ADD UNIQUE KEY `unique_wishlist` (`user_id`,`barang_id`),
  ADD KEY `barang_id` (`barang_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `tb_admin`
--
ALTER TABLE `tb_admin`
  MODIFY `admin_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `tb_barang`
--
ALTER TABLE `tb_barang`
  MODIFY `barang_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=26;

--
-- AUTO_INCREMENT for table `tb_custom_orders`
--
ALTER TABLE `tb_custom_orders`
  MODIFY `order_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `tb_detail_penjualan`
--
ALTER TABLE `tb_detail_penjualan`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=73;

--
-- AUTO_INCREMENT for table `tb_kategori`
--
ALTER TABLE `tb_kategori`
  MODIFY `kategori_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `tb_merk`
--
ALTER TABLE `tb_merk`
  MODIFY `merk_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT for table `tb_pembayaran`
--
ALTER TABLE `tb_pembayaran`
  MODIFY `pembayaran_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `tb_pembelian`
--
ALTER TABLE `tb_pembelian`
  MODIFY `id_pembelian` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=71;

--
-- AUTO_INCREMENT for table `tb_penjualan`
--
ALTER TABLE `tb_penjualan`
  MODIFY `penjualan_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=60;

--
-- AUTO_INCREMENT for table `tb_supplier`
--
ALTER TABLE `tb_supplier`
  MODIFY `supplier_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT for table `tb_user`
--
ALTER TABLE `tb_user`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- AUTO_INCREMENT for table `tb_wishlist`
--
ALTER TABLE `tb_wishlist`
  MODIFY `wishlist_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=29;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `tb_barang`
--
ALTER TABLE `tb_barang`
  ADD CONSTRAINT `tb_barang_ibfk_1` FOREIGN KEY (`merk_id`) REFERENCES `tb_merk` (`merk_id`),
  ADD CONSTRAINT `tb_barang_ibfk_2` FOREIGN KEY (`kategori_id`) REFERENCES `tb_kategori` (`kategori_id`);

--
-- Constraints for table `tb_detail_pembelian`
--
ALTER TABLE `tb_detail_pembelian`
  ADD CONSTRAINT `tb_detail_pembelian_ibfk_1` FOREIGN KEY (`barang_id`) REFERENCES `tb_barang` (`barang_id`),
  ADD CONSTRAINT `tb_detail_pembelian_ibfk_2` FOREIGN KEY (`id_pembelian`) REFERENCES `tb_pembelian` (`id_pembelian`);

--
-- Constraints for table `tb_detail_penjualan`
--
ALTER TABLE `tb_detail_penjualan`
  ADD CONSTRAINT `tb_detail_penjualan_ibfk_1` FOREIGN KEY (`penjualan_id`) REFERENCES `tb_penjualan` (`penjualan_id`),
  ADD CONSTRAINT `tb_detail_penjualan_ibfk_2` FOREIGN KEY (`barang_id`) REFERENCES `tb_barang` (`barang_id`);

--
-- Constraints for table `tb_pembelian`
--
ALTER TABLE `tb_pembelian`
  ADD CONSTRAINT `tb_pembelian_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `tb_user` (`user_id`),
  ADD CONSTRAINT `tb_pembelian_ibfk_2` FOREIGN KEY (`pembayaran_id`) REFERENCES `tb_pembayaran` (`pembayaran_id`);

--
-- Constraints for table `tb_penjualan`
--
ALTER TABLE `tb_penjualan`
  ADD CONSTRAINT `tb_penjualan_ibfk_1` FOREIGN KEY (`admin_id`) REFERENCES `tb_admin` (`admin_id`);

--
-- Constraints for table `tb_supplier`
--
ALTER TABLE `tb_supplier`
  ADD CONSTRAINT `tb_supplier_ibfk_1` FOREIGN KEY (`barang_id`) REFERENCES `tb_barang` (`barang_id`);

--
-- Constraints for table `tb_wishlist`
--
ALTER TABLE `tb_wishlist`
  ADD CONSTRAINT `tb_wishlist_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `tb_user` (`user_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `tb_wishlist_ibfk_2` FOREIGN KEY (`barang_id`) REFERENCES `tb_barang` (`barang_id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
