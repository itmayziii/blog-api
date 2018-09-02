INSERT INTO
  webpages (
    id,
    created_at,
    updated_at,
    created_by,
    last_updated_by,
    category_id,
    path,
    is_live,
    title,
    content,
    preview,
    image_path_sm,
    image_path_md,
    image_path_lg,
    image_path_meta
  )
SELECT
  null,
  created_at,
  updated_at,
  user_id,
  user_id,
  category_id,
  CONCAT('/posts/', slug),
  IF(status = 'live', 1, 0) as status,
  title,
  content,
  preview,
  image_path_sm,
  image_path_md,
  image_path_lg,
  image_path_meta
FROM
  posts;