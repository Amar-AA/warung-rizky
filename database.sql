CREATE TABLE produk (
    id INT PRIMARY KEY AUTO_INCREMENT,
    nama_produk VARCHAR(100),
    kategori ENUM('Digital', 'Sembako', 'Bumbu', 'Jajanan'),
    harga INT
);

INSERT INTO produk (nama_produk, kategori, harga) VALUES 
('Token Listrik 20rb', 'Digital', 22000),
('Paket Data 10GB', 'Digital', 55000),
('Top Up DANA 50rb', 'Digital', 51000),
('Beras Shira 5kg', 'Sembako', 68000),
('Minyak Goreng 1L', 'Sembako', 18000),
('Gula Pasir 1kg', 'Sembako', 16000);