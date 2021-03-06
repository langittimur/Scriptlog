<?php
/**
 * FrontPostDao extends Dao
 * 
 * 
 */
class FrontPostDao extends Dao 
{
 
private $linkPosts;

public function __construct()
{
  parent::__construct();
}

/**
 * getPostFeeds
 * Retrieve posts records for sharing post on post feeds
 *
 * @param integer $limit
 * @return void
 */
public function getPostFeeds($limit = 5)
{
  $sql =  "SELECT p.ID, p.media_id, p.post_author,
                  p.post_date, p.post_modified, p.post_title,
                  p.post_slug, p.post_content, p.post_tags, p.post_type,
                  p.post_status, u.user_login
            FROM tbl_posts AS p
            INNER JOIN tbl_users AS u ON p.post_author = u.ID
            WHERE p.post_type = 'blog' AND p.post_status = 'publish'
            ORDER BY p.ID DESC LIMIT :limit";

  $data = array(':limit' => $limit);

  $this->setSQL($sql);

  $feeds = $this->findAll($data);

  return (empty($feeds)) ?: $feeds;

}

/**
 * getPostById
 * retrieving detail post record by Id
 *
 * @param integer $id
 * @param object $sanitize
 * @return boolean|array|object
 *
 */
public function getPostById($id, $sanitize)
{
    $sql = "SELECT p.ID, p.media_id, p.post_author, p.post_date, p.post_modified, p.post_title, p.post_slug,
            p.post_content, p.post_summary, p.post_keyword, p.post_tags, p.post_status, p.post_type, 
            p.comment_status, m.media_filename, m.media_caption, m.media_target, m.media_access, 
            u.user_fullname
    FROM tbl_posts AS p
    INNER JOIN tbl_media AS m ON p.media_id = m.ID
    INNER JOIN tbl_users AS u ON p.post_author = u.ID
    WHERE p.ID = :ID AND p.post_status = 'publish'
    AND p.post_type = 'blog' AND m.media_target = 'blog'
    AND m.media_access = 'public' AND m.media_status = '1'";

    $sanitized_id = $this->filteringId($sanitize, $id, 'sql');

    $this->setSQL($sql);

    $postById = $this->findRow([':ID' => (int)$sanitized_id]);

    return (empty($postById)) ?: $postById;

}

/**
 * getPostBySlug
 * retrieving post record by slug
 *
 * @param string $slug
 * @return mixed
 *
 */
public function getPostBySlug($slug)
{

  $sql = "SELECT p.ID, p.media_id, p.post_author,
                 p.post_date, p.post_modified, p.post_title,
                 p.post_slug, p.post_content, p.post_summary,
                 p.post_keyword, p.post_tags,
                 p.post_status, p.post_type, p.comment_status, 
                 u.user_login
          FROM tbl_posts AS p
          INNER JOIN tbl_users AS u ON p.post_author = u.ID
          WHERE p.post_slug = :slug AND p.post_type = 'blog'";

  $this->setSQL($sql);

  $postBySlug = $this->findRow([':slug' => $slug]);

  return (empty($postBySlug)) ?: $postBySlug;

}

/**
 * getPostsPublished
 * retrieving all records published
 *
 * @param Paginator $perPage
 * @param object $sanitize
 * @return boolean|array[]|object[]|string[]
 *
 */
public function getPostsPublished(Paginator $perPage, $sanitize)
{

    $pagination = null;

    $this->linkPosts = $perPage;

    $stmt = $this->dbc->dbQuery("SELECT ID FROM tbl_posts WHERE post_status = 'publish' AND post_type = 'blog'");

    $this->linkPosts->set_total($stmt -> rowCount());

    $sql = "SELECT p.ID, p.media_id, p.post_author,
                     p.post_date, p.post_modified, p.post_title,
                     p.post_slug, p.post_content, p.post_summary,
                     p.post_keyword, p.post_tags,
                     p.post_type, p.post_status, u.user_login, u.user_fullname,
                     m.media_filename, m.media_caption
  			FROM tbl_posts AS p
  			INNER JOIN tbl_users AS u ON p.post_author = u.ID
        INNER JOIN tbl_media AS m ON p.media_id = m.ID
  			WHERE p.post_type = 'blog' AND p.post_status = 'publish'
  			ORDER BY p.ID DESC " . $this->linkPosts->get_limit($sanitize);

    $this->setSQL($sql);

    $postsPublished = $this->findAll();

    $pagination = $this->linkPosts->page_links($sanitize);

    return (empty($postsPublished)) ?: ['postsPublished' => $postsPublished, 'paginationLink' => $pagination];

}

/**
 * getHeadlinesPosts
 * retrieving headline posts
 *
 * @param int $start
 * @param int $limit
 * @return void
 * 
 */
public function getHeadlinesPosts($start, $limit)
{

$sql = "SELECT p.ID, p.media_id, p.post_author,
        p.post_date, p.post_modified, p.post_title,
        p.post_slug, p.post_content, p.post_summary,
        p.post_keyword, p.post_tags,
        p.post_type, p.post_status, u.user_login, u.user_fullname,
        m.media_filename, m.media_caption, m.media_target, m.media_access
FROM tbl_posts AS p
INNER JOIN tbl_users AS u ON p.post_author = u.ID
INNER JOIN tbl_media AS m ON p.media_id = m.ID
WHERE p.post_type = 'blog' AND p.post_status = 'publish'
ORDER BY p.ID LIMIT :start, :limit";

$this->setSQL($sql);

$headlines = $this->findAll([':start' => $start, ':limit' => $limit]);

return (empty($headlines)) ?: $headlines;

}

/**
 * getRelatedPosts
 * retrieving related post records
 *
 * @param string $post_title
 * @return mixed
 *
 */
public function getRelatedPosts($post_title)
{

  $sql = "SELECT ID, media_id, post_author, post_date, post_modified,
                 post_title, post_slug, post_content, post_tags, MATCH(post_title, post_content, post_tags)
                 AGAINST(?) AS score
          FROM tbl_posts WHERE MATCH(post_title, post_content) AGAINTS(?)
          ORDER BY score ASC LIMIT 3";

  $this->setSQL($sql);

  $relatedPosts = $this->findRow([$post_title]);

  return (empty($relatedPosts)) ?: $relatedPosts;

}

/**
 * getRandomPosts
 *
 * @param int $limit
 * @return array
 *
 */
public function getRandomPosts($limit)
{

  $sql = "SELECT p.ID, p.media_id, p.post_author, p.post_date, p.post_modified, p.post_title, p.post_slug, p.post_content, p.post_tags,
          m.ID, m.media_filename, m.media_caption
          FROM tbl_posts AS P
          INNER JOIN
            (SELECT ROUND(RAND() * (SELECT MAX(ID) FROM tbl_media )) AS id ) AS m
          WHERE p.media_id >= m.ID
          LIMIT :limit";

  $this->setSQL($sql);

  $data = array(':limit' => (int)$limit);

  $randomPosts = $this->findAll($data);

  return (empty($randomPosts)) ?: $randomPosts;

}

/**
 * getNextPost
 *
 * @param int $postId
 * @param object $sanitize
 * @param string $fetchMode
 * @return void
 * 
 */
public function getNextPost($postId, $sanitize, $fetchMode = null)
{

  $id_sanitized = $this->filteringId($sanitize, $postId, 'sql');

  $sql = "SELECT ID, post_title, post_slug, post_type
          FROM tbl_posts WHERE ID > :ID
          AND post_status = 'publish' AND post_type = 'blog'
          ORDER BY ID LIMIT 1";

  $this->setSQL($sql);

  $nextPost = (is_null($fetchMode)) ? $this->findRow([':ID' => $id_sanitized]) : $this->findRow([':ID' => $id_sanitized], $fetchMode);

  return (empty($nextPost)) ?: $nextPost;

}

/**
 * getPrevPost
 * 
 * @param int $postId
 * @param object $sanitize
 * @param string $fetchMode
 * @return void
 * 
 */
public function getPrevPost($postId, $sanitize, $fetchMode = null)
{
  
  $id_sanitized = $this->filteringId($sanitize, $postId, 'sql');

  $sql = "SELECT ID, post_title, post_slug, post_type
          FROM tbl_posts WHERE ID < :ID
          AND post_status = 'publish' AND post_type = 'blog'
          ORDER BY ID LIMIT 1";

  $this->setSQL($sql);

  $prevPost = (is_null($fetchMode)) ? $this->findRow([':ID' => $id_sanitized]) : $this->findRow([':ID' => $id_sanitized], $fetchMode);

  return (empty($prevPost)) ?: $prevPost;

}

/**
 * getPostsOnSidebar
 *
 * @param string $status
 * @param int|num $start
 * @param int|num $limit
 * @return void
 * 
 */
public function getPostsOnSidebar($status, $start, $limit)
{

$sql = "SELECT p.ID, p.media_id, p.post_author,
               p.post_date, p.post_modified, p.post_title,
               p.post_slug, p.post_content, p.post_summary,
               p.post_keyword, p.post_tags,
               p.post_type, p.post_status, u.user_login, u.user_fullname,
               m.ID, m.media_filename, m.media_caption
  FROM tbl_posts AS p
  INNER JOIN tbl_users AS u ON p.post_author = u.ID
  INNER JOIN tbl_media AS m ON p.media_id = m.ID
  WHERE p.post_type = 'blog' AND p.post_status = :status
  ORDER BY p.ID DESC LIMIT :position, :limit ";

$this->setSQL($sql);

$sidebar_posts = $this->findAll([':status' => $status, ':position'=> $start, ':limit' => $limit]);

return (empty($sidebar_posts)) ?: ['sidebarPosts' => $sidebar_posts];

}

}