-- Add latitude and longitude columns to produk table
ALTER TABLE produk
ADD COLUMN latitude DECIMAL(10, 8) NULL AFTER alamat,
ADD COLUMN longitude DECIMAL(11, 8) NULL AFTER latitude;

-- Add an index for better geospatial queries
ALTER TABLE produk
ADD SPATIAL INDEX `idx_location` (longitude, latitude);
