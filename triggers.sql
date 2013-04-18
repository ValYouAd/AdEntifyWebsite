# Mise à jour du compteur de commentaires pour la photo qui vient d'être commentée
CREATE TRIGGER comments_count AFTER INSERT ON comment
 FOR EACH ROW UPDATE photo SET comments_count = comments_count+1
WHERE id = NEW.photo_id;

# Mise à jour du compteur de likes pour la photo qui vient d'être liké
CREATE TRIGGER likes_count AFTER INSERT ON `like`
 FOR EACH ROW UPDATE photo SET likes_count = likes_count+1
WHERE id = NEW.photo_id;

# Mise à jour compteurs de tags
DELIMITER $$
CREATE TRIGGER tags_count AFTER INSERT ON `tag`
 FOR EACH ROW BEGIN
    UPDATE photo SET tags_count = tags_count+1 WHERE id = NEW.photo_id;
    UPDATE brand SET tags_count = tags_count+1 WHERE id = NEW.brand_id;
    UPDATE venue SET tags_count = tags_count+1 WHERE id = NEW.purchaseVenue_id;
 END$$
DELIMITER ;