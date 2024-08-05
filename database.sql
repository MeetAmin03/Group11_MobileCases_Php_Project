-- Drop the database if it exists and create a new one
DROP DATABASE IF EXISTS mobile_case_store;
CREATE DATABASE mobile_case_store;
USE mobile_case_store;

-- Create the Users table
CREATE TABLE Users (
    user_id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL,
    password VARCHAR(255) NOT NULL,
    first_name VARCHAR(50),
    last_name VARCHAR(50),
    mobile VARCHAR(20),
    email VARCHAR(100) NOT NULL,
    userType ENUM('admin', 'user') NOT NULL DEFAULT 'user',
    is_active BOOLEAN NOT NULL DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);


-- Create the Addresses table
CREATE TABLE Addresses (
    address_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    first_name VARCHAR(50) NOT NULL,
    last_name VARCHAR(50) NOT NULL,
    street VARCHAR(255) NOT NULL,
    city VARCHAR(100) NOT NULL,
    state VARCHAR(100) NOT NULL,
    postal_code VARCHAR(20) NOT NULL,
    country VARCHAR(100) NOT NULL,
    mobile VARCHAR(20),
    email VARCHAR(100),
    is_active BOOLEAN NOT NULL DEFAULT TRUE,
    FOREIGN KEY (user_id) REFERENCES Users(user_id),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Create the Brands table
CREATE TABLE Brands (
    brand_id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    is_active BOOLEAN NOT NULL DEFAULT TRUE,
    description TEXT
);


-- Create the Products table
CREATE TABLE Products (
    product_id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    short_description VARCHAR(255),
    description TEXT NOT NULL,
    price DECIMAL(10, 2) NOT NULL,
    brand_id INT,
    image VARCHAR(255) NOT NULL, -- Column for image filenames
    is_active BOOLEAN NOT NULL DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (brand_id) REFERENCES Brands(brand_id)
    
);

-- Create the Orders table
CREATE TABLE Orders (
    order_id INT AUTO_INCREMENT PRIMARY KEY,
    order_number VARCHAR(50) NOT NULL UNIQUE,
    user_id INT NOT NULL,
    total DECIMAL(10, 2) NOT NULL,
    address_id INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    status ENUM('Pending', 'Processing', 'Completed', 'Cancelled', 'Delivered') NOT NULL DEFAULT 'Pending',
    FOREIGN KEY (user_id) REFERENCES Users(user_id),
    FOREIGN KEY (address_id) REFERENCES Addresses(address_id)
);


-- Create the OrderItems table
CREATE TABLE OrderItems (
    order_item_id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT NOT NULL,
    product_id INT NOT NULL,
    quantity INT NOT NULL,
    price DECIMAL(10, 2) NOT NULL,
    FOREIGN KEY (order_id) REFERENCES Orders(order_id),
    FOREIGN KEY (product_id) REFERENCES Products(product_id)
);



INSERT INTO Brands (name, description) VALUES
('Apple', 'Apple Inc. is an American multinational technology company headquartered in Cupertino, California. Apple is known for its innovative products such as the iPhone, iPad, and Mac computers.'),
('Samsung', 'Samsung Electronics is a South Korean multinational electronics company headquartered in Suwon, South Korea. Samsung is known for its wide range of consumer electronics, including smartphones, tablets, and televisions.'),
('Google', 'Google LLC is an American multinational technology company that specializes in Internet-related services and products, which include online advertising technologies, a search engine, cloud computing, software, and hardware.');



INSERT INTO Products (name, short_description, description, price, brand_id, image) VALUES
('Samsung S24 Ultra Snake Case', 'Stylish snake case for Samsung S24 Ultra', '<ul><li>Stylish design with unique snake pattern</li><li>Protects against scratches and drops</li><li>Available in multiple colors</li><li>Easy to install and remove</li><li>High-quality material for durability</li><li>Custom fit for Samsung S24 Ultra</li><li>Precise cutouts for all ports</li><li>Anti-slip grip for secure handling</li><li>Lightweight and slim design</li><li>Perfect for daily use</li></ul>', 22.99, 2, 'samsungS24_ultra_snake_case.jpg'),
('Samsung S23 FE Mirror Case', 'Elegant mirror case for Samsung S23 FE', '<ul><li>Mirror-like finish for a sleek look</li><li>Protects your phone from everyday bumps</li><li>Easy access to all ports and buttons</li><li>High-definition mirror reflection</li><li>Elegant and modern design</li><li>Resistant to fingerprints and smudges</li><li>Shock-absorbing material for extra protection</li><li>Custom fit for Samsung S23 FE</li><li>Thin and lightweight</li><li>Available in multiple colors</li></ul>', 18.49, 2, 'samsungS23_FE_mirror_case.jpg'),
('Samsung S24 Ultra Black Case', 'Sleek black case for Samsung S24 Ultra', '<ul><li>Matte black finish for a modern look</li><li>Shock-absorbing materials</li><li>Perfect fit for Samsung S24 Ultra</li><li>Offers excellent drop protection</li><li>Anti-scratch surface to maintain quality</li><li>Precision cutouts for camera and ports</li><li>Ergonomic design for comfortable grip</li><li>Slim profile that fits easily in pockets</li><li>High-quality construction for durability</li><li>Easy to clean and maintain</li></ul>', 20.99, 2, 'samsungS24_ultra_black_case.jpg'),
('Samsung S23 FE Flower Case', 'Beautiful flower case for Samsung S23 FE', '<ul><li>Flower design adds a touch of elegance</li><li>Durable and long-lasting material</li><li>Protects against scratches and drops</li><li>Vibrant colors and intricate design</li><li>Custom fit for Samsung S23 FE</li><li>Resilient against everyday wear and tear</li><li>Easy access to all buttons and ports</li><li>Anti-slip texture for better grip</li><li>Thin and lightweight for comfort</li><li>Perfect gift for floral design enthusiasts</li></ul>', 19.99, 2, 'samsungS23_FE_flower_case.jpg'),
('iPhone 15 Pro Transparent Case', 'Clear transparent case for iPhone 15 Pro', '<ul><li>Crystal-clear design to showcase the phone</li><li>Protection against scratches and minor drops</li><li>Custom fit for iPhone 15 Pro</li><li>Maintains the original look of your phone</li><li>Flexible and easy to install</li><li>Resilient to yellowing over time</li><li>Precise cutouts for ports and camera</li><li>Thin and lightweight design</li><li>Enhances grip with anti-slip texture</li><li>Ideal for those who want minimal coverage</li></ul>', 24.99, 1, 'iphone15_pro_transparent_case.jpg'),
('iPhone 15 Pro Max Gray Case', 'Elegant gray case for iPhone 15 Pro Max', '<ul><li>Subtle gray color for a professional look</li><li>Robust protection with enhanced shock absorption</li><li>Fits iPhone 15 Pro Max perfectly</li><li>Premium material for a luxurious feel</li><li>Anti-scratch surface to keep it looking new</li><li>Easy to install and remove</li><li>All ports and buttons accessible</li><li>Anti-slip grip for secure handling</li><li>Slim and lightweight design</li><li>Great for everyday use</li></ul>', 27.99, 1, 'iphone15_pro_max_gray_case.jpg'),
('iPhone 15 Pro Max Black Case', 'Sleek black case for iPhone 15 Pro Max', '<ul><li>Sleek design with a premium finish</li><li>Enhanced protection against drops and impacts</li><li>Available for iPhone 15 Pro Max</li><li>Robust material ensures durability</li><li>Anti-slip texture for a better grip</li><li>Custom fit with precise cutouts</li><li>Easy to clean and maintain</li><li>Thin profile fits comfortably in your pocket</li><li>Resistant to everyday wear and tear</li><li>Perfect for those who prefer a minimalist look</li></ul>', 23.99, 1, 'iphone15_pro_max_black_case.jpg'),
('iPhone 15 Pro Brown Case', 'Stylish brown case for iPhone 15 Pro', '<ul><li>Rich brown color with a luxurious feel</li><li>Provides excellent drop protection</li><li>Perfectly fits the iPhone 15 Pro</li><li>High-quality material for long-lasting use</li><li>Anti-scratch surface</li><li>Easy to install and remove</li><li>All ports and buttons remain accessible</li><li>Elegant design suitable for formal occasions</li><li>Anti-slip grip for secure handling</li><li>Maintains the original look of your phone</li></ul>', 25.49, 1, 'iphone15_pro_brown_case.jpg'),
('iPhone 15 Chumma Case', 'Unique chumma case for iPhone 15', '<ul><li>Distinctive chumma design</li><li>Offers durable protection for your phone</li><li>Compatible with iPhone 15</li><li>High-quality construction for longevity</li><li>Anti-slip texture for better grip</li><li>Easy to install and remove</li><li>Custom fit with precise cutouts</li><li>Resilient to everyday wear and tear</li><li>Vibrant and unique design</li><li>Perfect for those who love unique styles</li></ul>', 21.99, 1, 'iphone15_chumma_case.jpg'),
('Google Pixel 8 Tiger Case', 'Eye-catching tiger case for Google Pixel 8', '<ul><li>Bold tiger design for a striking look</li><li>Provides reliable protection</li><li>Custom fit for Google Pixel 8</li><li>Durable material for everyday use</li><li>Anti-scratch and anti-slip surface</li><li>Easy to install and remove</li><li>All ports and buttons remain accessible</li><li>Lightweight and slim design</li><li>Maintains the original look of your phone</li><li>Perfect for animal print enthusiasts</li></ul>', 19.49, 3, 'google_pixel_8_tiger_case.jpg'),
('Google Pixel 8 Sea Case', 'Beautiful sea case for Google Pixel 8', '<ul><li>Sea-themed design with vibrant colors</li><li>Durable and protective</li><li>Fits Google Pixel 8 perfectly</li><li>Anti-scratch and anti-slip material</li><li>Custom fit with precise cutouts</li><li>Easy to clean and maintain</li><li>Thin and lightweight for comfort</li><li>Resilient against everyday wear and tear</li><li>Elegant design suitable for casual use</li><li>Ideal for those who love ocean themes</li></ul>', 18.99, 3, 'google_pixel_8_sea_case.jpg'),
('Google Pixel 8 Pro Jon Black Case', 'Sleek jon black case for Google Pixel 8 Pro', '<ul><li>Elegant jon black finish</li><li>Protects against drops and scratches</li><li>Custom fit for Google Pixel 8 Pro</li><li>High-quality material for durability</li><li>Anti-slip texture for secure handling</li><li>All ports and buttons remain accessible</li><li>Easy to install and remove</li><li>Thin and lightweight design</li><li>Resilient to everyday wear and tear</li><li>Great for minimalist style lovers</li></ul>', 21.99, 3, 'google_pixel_8_pro_jon_black_case.jpg'),
('Google Pixel 8 Pro Bhoot Case', 'Unique bhoot case for Google Pixel 8 Pro', '<ul><li>Spooky bhoot design for a unique look</li><li>Enhanced protection for Google Pixel 8 Pro</li><li>Durable and long-lasting</li><li>Custom fit with precise cutouts</li><li>Anti-slip texture for better grip</li><li>Easy to install and remove</li><li>All ports and buttons remain accessible</li><li>High-quality material for long-term use</li><li>Elegant and stylish design</li><li>Perfect for those who enjoy quirky styles</li></ul>', 23.99, 3, 'google_pixel_8_pro_bhoot_case.jpg'),
('Galaxy S23 Nature Case', 'Nature-themed case for Galaxy S23', '<ul><li>Nature-inspired design with natural textures</li><li>Reliable drop protection</li><li>Fits Galaxy S23 perfectly</li><li>Anti-scratch and anti-slip surface</li><li>Custom fit with precise cutouts</li><li>Easy to clean and maintain</li><li>Lightweight and slim for comfort</li><li>Resilient against everyday wear and tear</li><li>Ideal for nature enthusiasts</li><li>Elegant design suitable for casual use</li></ul>', 20.49, 2, 'galaxy_s23_nature_case.jpg'),
('Galaxy S23 Black Case', 'Sleek black case for Galaxy S23', '<ul><li>Sleek black design with minimalistic style</li><li>Provides robust protection</li><li>Perfect fit for Galaxy S23</li><li>Anti-scratch and anti-slip surface</li><li>High-quality material for durability</li><li>Easy to install and remove</li><li>All ports and buttons remain accessible</li><li>Thin and lightweight design</li><li>Resilient against everyday wear and tear</li><li>Great for those who prefer a minimalist look</li></ul>', 21.49, 2, 'galaxy_s23_black_case.jpg');



