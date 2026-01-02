
-- Create users table (for authentication)
CREATE TABLE IF NOT EXISTS users (
    user_id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role ENUM('user', 'admin') DEFAULT 'user',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Create categories table
CREATE TABLE IF NOT EXISTS categories (
    category_id INT AUTO_INCREMENT PRIMARY KEY,
    category_name VARCHAR(50) NOT NULL UNIQUE,
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Create articles table
CREATE TABLE IF NOT EXISTS articles (
    article_id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    content TEXT NOT NULL,
    summary TEXT,
    image_url VARCHAR(255),
    category_id INT,
    author_id INT,
    published_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    is_featured BOOLEAN DEFAULT FALSE,
    is_breaking BOOLEAN DEFAULT FALSE,
    views INT DEFAULT 0,
    FOREIGN KEY (category_id) REFERENCES categories(category_id) ON DELETE SET NULL,
    FOREIGN KEY (author_id) REFERENCES users(user_id) ON DELETE SET NULL
);

-- Create comments table
CREATE TABLE IF NOT EXISTS comments (
    comment_id INT AUTO_INCREMENT PRIMARY KEY,
    article_id INT NOT NULL,
    user_id INT,
    comment_text TEXT NOT NULL,
    timestamp TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    is_approved BOOLEAN DEFAULT TRUE,
    FOREIGN KEY (article_id) REFERENCES articles(article_id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE SET NULL
);

-- Insert default categories
INSERT INTO categories (category_name, description) VALUES
('Politics', 'Political news and government updates'),
('Technology', 'Latest technology trends and innovations'),
('Sports', 'Sports news and updates'),
('Entertainment', 'Entertainment industry news'),
('Business', 'Business and economic news'),
('Health', 'Health and medical news'),
('World', 'International news and events'),
('Science', 'Scientific discoveries and research');

-- Insert default admin user (password: admin123)
INSERT INTO users (username, email, password, role) VALUES
('admin', 'admin@globalnews.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin');

-- Insert sample articles
INSERT INTO articles (title, content, summary, image_url, category_id, author_id, is_featured, is_breaking) VALUES
('Breaking: Major Technology Breakthrough Announced', 
'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat. Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur. Excepteur sint occaecat cupidatat non proident, sunt in culpa qui officia deserunt mollit anim id est laborum.',
'A groundbreaking technology announcement that will change the industry.',
'https://fuzehub.com/wp-content/uploads/2018/09/MIT-Blog-Graphic.jpg',
2, 1, TRUE, TRUE),

('Sports Championship Finals This Weekend', 
'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat.',
'The most anticipated sports event of the year is happening this weekend.',
'https://ichef.bbci.co.uk/ace/standard/624/cpsprodpb/5F03/production/_108932342_gettyimages-594803882.jpg',
3, 1, TRUE, FALSE),

('New Economic Policies Announced', 
'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris.',
'Government announces new economic policies to boost growth.',
'https://www.avatrade.co.za/wp-content/images/blog/economic_policy.jpg',
1, 1, FALSE, FALSE),

('Health Study Reveals Important Findings', 
'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.',
'New health study provides insights into preventive care.',
'https://images.ctfassets.net/szez98lehkfm/XwLGeQZ1dTcvQotdrrW7n/ee03393071234624d75e7b379d4ba4be/MyIC_Article_96352?w=730&h=410&fm=jpg&fit=fill',
6, 1, FALSE, FALSE);

