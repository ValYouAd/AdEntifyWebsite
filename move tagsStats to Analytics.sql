INSERT INTO `analytics` (`photo_id`, `tag_id`, `user_id`, `action`, `element`, `created_at`, `ip_address`, `platform`, `link`)
SELECT tags.photo_id, tag_id, user_id, stat_type, 'tag', tag_stats.created_at, ip_address, platform, tag_stats.link
FROM tag_stats
LEFT JOIN tags
ON tag_stats.tag_id = tags.id