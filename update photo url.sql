UPDATE photos as pp
  INNER JOIN (
    SELECT CONCAT('http://localhost/AdEntifyFacebookApp/web/uploads/photos/users/', owner_id, '/original/', original_url) as new_original_url,
      CONCAT('http://localhost/AdEntifyFacebookApp/web/uploads/photos/users/', owner_id, '/large/', large_url) as new_large_url,
      CONCAT('http://localhost/AdEntifyFacebookApp/web/uploads/photos/users/', owner_id, '/medium/', medium_url) as new_medium_url,
      CONCAT('http://localhost/AdEntifyFacebookApp/web/uploads/photos/users/', owner_id, '/small/', small_url) as new_small_url, id
    FROM photos
  )
  AS p ON p.id = pp.id SET original_url = new_original_url, medium_url = p.new_medium_url, large_url = p.new_large_url, small_url = p.new_small_url


UPDATE brands as bb
  INNER JOIN (
    SELECT
      CONCAT('https://s3-eu-west-1.amazonaws.com/cdn.adentify.com/', large_logo_url) as new_large_url,
      CONCAT('https://s3-eu-west-1.amazonaws.com/cdn.adentify.com/', medium_logo_url) as new_medium_url,
      CONCAT('https://s3-eu-west-1.amazonaws.com/cdn.adentify.com/', small_logo_url) as new_small_url, id
    FROM brands
  )
  AS b ON b.id = bb.id SET medium_logo_url = b.new_medium_url, large_logo_url = b.new_large_url, small_logo_url = b.new_small_url


  UPDATE brands SET large_logo_url = replace(large_logo_url, 'https://cdn.adentify.com/', 'https://s3-eu-west-1.amazonaws.com/cdn.adentify.com/'),
  medium_logo_url = replace(medium_logo_url, 'https://cdn.adentify.com/', 'https://s3-eu-west-1.amazonaws.com/cdn.adentify.com/'),
  small_logo_url = replace(small_logo_url, 'https://cdn.adentify.com/', 'https://s3-eu-west-1.amazonaws.com/cdn.adentify.com/')