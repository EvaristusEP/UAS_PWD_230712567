-- Ubah kolom user_id di tabel orders menjadi nullable
-- Ini memungkinkan orders tetap ada meskipun user dihapus (untuk laporan)

ALTER TABLE orders 
MODIFY COLUMN user_id INT NULL;

-- Ubah foreign key constraint menjadi SET NULL saat user dihapus
ALTER TABLE orders 
DROP FOREIGN KEY orders_ibfk_1;

ALTER TABLE orders 
ADD CONSTRAINT orders_ibfk_1 
FOREIGN KEY (user_id) REFERENCES users(id) 
ON DELETE SET NULL;
