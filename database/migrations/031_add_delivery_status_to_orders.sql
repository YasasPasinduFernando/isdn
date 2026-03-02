-- ============================================
-- Add Delivery Status Values to Orders Table
-- ============================================

-- Modify the status ENUM to include delivery-related statuses
ALTER TABLE `orders` 
MODIFY COLUMN `status` ENUM(
    'pending', 
    'confirmed', 
    'processing', 
    'out_for_delivery',
    'arrived',
    'delivered', 
    'failed',
    'cancelled'
) DEFAULT 'pending';
