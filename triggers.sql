# Mise à jour du compteur de commentaires pour la photo qui vient d'être commentée
CREATE TRIGGER photo_comments_count AFTER INSERT ON comment
 FOR EACH ROW UPDATE photo SET comments_count = comments_count+1
WHERE id = NEW.photo_id

# Mise à jour du compteur de likes pour la photo qui vient d'être liké
CREATE TRIGGER photo_likes_count AFTER INSERT ON `like`
 FOR EACH ROW UPDATE photo SET likes_count = likes_count+1
WHERE id = NEW.photo_id

# Mise à jour du compteur de tags pour la photo qui vient d'être tagué
CREATE TRIGGER photo_tags_count AFTER INSERT ON `tag`
 FOR EACH ROW UPDATE photo SET tags_count = tags_count+1
WHERE id = NEW.photo_id