UPDATE photos as pp
  INNER JOIN (
    SELECT CONCAT('http://localhost/AdEntifyFacebookApp/web/uploads/photos/users/', owner_id, '/original/', original_url) as new_original_url,
      CONCAT('http://localhost/AdEntifyFacebookApp/web/uploads/photos/users/', owner_id, '/large/', large_url) as new_large_url,
      CONCAT('http://localhost/AdEntifyFacebookApp/web/uploads/photos/users/', owner_id, '/medium/', medium_url) as new_medium_url,
      CONCAT('http://localhost/AdEntifyFacebookApp/web/uploads/photos/users/', owner_id, '/small/', small_url) as new_small_url, id
    FROM photos
  )
  AS p ON p.id = pp.id SET medium_url = p.new_medium_url, large_url = p.new_large_url, small_url = p.new_small_url