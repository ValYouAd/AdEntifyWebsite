# Mise à jour du compteur de commentaires pour la photo qui vient d'être commentée
DROP TRIGGER IF EXISTS  `comments_count`;
CREATE TRIGGER comments_count AFTER INSERT ON comments
 FOR EACH ROW UPDATE photos SET comments_count = comments_count+1 WHERE id = NEW.photo_id;

# Mise à jour du compteur de likes pour la photo qui vient d'être liké
DROP TRIGGER IF EXISTS  `likes_count`;
CREATE TRIGGER likes_count AFTER INSERT ON `likes`
 FOR EACH ROW UPDATE photos SET likes_count = likes_count+1 WHERE id = NEW.photo_id;

# Mise à jour compteurs de tags
DROP TRIGGER IF EXISTS  `tags_count`;
DELIMITER $$
CREATE TRIGGER tags_count AFTER INSERT ON `tags`
 FOR EACH ROW BEGIN
    UPDATE photos SET tags_count = tags_count+1 WHERE id = NEW.photo_id;
    UPDATE venues SET tags_count = tags_count+1 WHERE id = NEW.venue_id;
    UPDATE products SET tags_count = tags_count+1 WHERE id = NEW.product_id;
    UPDATE people SET tags_count = tags_count+1 WHERE id = NEW.person_id;
    UPDATE brands b JOIN products p ON p.brand_id = p.id SET b.tags_count = b.tags_count+1 WHERE p.id = NEW.product_id;
 END$$
DELIMITER ;

# Mise à jour des compteurs de produits
DROP TRIGGER IF EXISTS  `products_count`;
DELIMITER $$
CREATE TRIGGER products_count AFTER INSERT ON `products`
 FOR EACH ROW BEGIN
    UPDATE brands SET products_count = products_count+1 WHERE id = NEW.brand_id;
    UPDATE venues SET products_count = products_count+1 WHERE id = NEW.purchaseVenue_id;
 END$$
DELIMITER ;

# Mise à jour compteurs de photos
DROP TRIGGER IF EXISTS  `photos_count`;
DELIMITER $$
CREATE TRIGGER photos_count AFTER INSERT ON `photos`
 FOR EACH ROW BEGIN
    UPDATE users u SET photos_count = photos_count+1 WHERE u.id = NEW.owner_id;
    UPDATE venues v SET photos_count = photos_count+1 WHERE v.id = NEW.venue_id;
 END$$
DELIMITER ;

# Mise à jour des compteurs de lieux
DROP TRIGGER IF EXISTS  `venues_count`;
DELIMITER $$
CREATE TRIGGER venues_count AFTER INSERT ON `brand_venue`
 FOR EACH ROW BEGIN
    UPDATE brands b SET b.venues_count = b.venues_count+1 WHERE b.id = NEW.brand_id;
 END$$
DELIMITER ;