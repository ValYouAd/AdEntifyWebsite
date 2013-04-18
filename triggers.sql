# Mise à jour du compteur de commentaires pour la photo qui vient d'être commentée
DROP TRIGGER IF EXISTS  `comments_count`;
CREATE TRIGGER comments_count AFTER INSERT ON comment
 FOR EACH ROW UPDATE photo SET comments_count = comments_count+1 WHERE id = NEW.photo_id;

# Mise à jour du compteur de likes pour la photo qui vient d'être liké
DROP TRIGGER IF EXISTS  `likes_count`;
CREATE TRIGGER likes_count AFTER INSERT ON `like`
 FOR EACH ROW UPDATE photo SET likes_count = likes_count+1 WHERE id = NEW.photo_id;

# Mise à jour compteurs de tags
DROP TRIGGER IF EXISTS  `tags_count`;
DELIMITER $$
CREATE TRIGGER tags_count AFTER INSERT ON `tag`
 FOR EACH ROW BEGIN
    UPDATE photo SET tags_count = tags_count+1 WHERE id = NEW.photo_id;
    UPDATE venue SET tags_count = tags_count+1 WHERE id = NEW.venue_id;
    UPDATE product SET tags_count = tags_count+1 WHERE id = NEW.product_id;
    UPDATE person SET tags_count = tags_count+1 WHERE id = NEW.person_id;
    UPDATE brand b JOIN product p ON p.brand_id = p.id SET b.tags_count = b.tags_count+1 WHERE p.id = NEW.product_id;
 END$$
DELIMITER ;

# Mise à jour des compteurs de produits
DROP TRIGGER IF EXISTS  `products_count`;
DELIMITER $$
CREATE TRIGGER products_count AFTER INSERT ON `product`
 FOR EACH ROW BEGIN
    UPDATE brand SET products_count = products_count+1 WHERE id = NEW.brand_id;
    UPDATE venue SET products_count = products_count+1 WHERE id = NEW.purchaseVenue_id;
 END$$
DELIMITER ;