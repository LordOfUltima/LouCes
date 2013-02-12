<?php
/*
PHPLoU_bot - an LoU bot writen in PHP
Copyright (C) 2011 Roland Braband

This program is free software; you can redistribute it and/or modify it under the terms of the GNU General Public License as published by the Free Software Foundation; either version 2 of the License, or (at your option) any later version.

This program is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License for more details.

You should have received a copy of the GNU General Public License along with this program; if not, write to the Free Software Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA 02111-1307 USA
*/
define('FORUM', 'FORUM');
define('THREAD', 'THREAD');
define('POST', 'POST');

class Forum {
  private $lou;
  public $forums  = array();
  public $threads = array();
  public $posts   = array();
  
  static function factory($lou) {
        
    // New Forum Object
    $forum = new Forum($lou);
    // Return the object
    return $forum;
  }
  
  public function __construct($lou) {
      $this->lou =& $lou;
  }
  
  public function exist_forum_id($forum_id, $force = false) {
    if (empty($this->forums) || $force) $this->get_alliance_forums();
    if ($this->forums['data'][$forum_id]['id'] == intval($forum_id)) return true;
    else return false;
  }
  
  public function exist_forum_thread_id($forum_id, $thread_id, $force = false) {
    if (!$this->exist_forum_id($forum_id)) return false;
    if (empty($this->threads[$forum_id]) || $force) $this->get_alliance_forum_threads($forum_id);
    if ($this->threads[$forum_id]['data'][$thread_id]['id'] == intval($thread_id)) return true;
    else return false;
  }
  
  public function get_forum_id_by_name($name, $create = false) {
    if ($this->get_alliance_forums()) {
      if (is_array($this->forums['data'])) foreach($this->forums['data'] as $forum) {
        if ($forum['name'] == trim($name)) return $forum['id'];
      }
    }
    if ($create) {
      return  $this->create_alliance_forum($name);
    } else return false;
  }
  
  public function get_forum_id_by_exp($expression) {
    if ($this->get_alliance_forums()) {
      if (is_array($this->forums['data'])) foreach($this->forums['data'] as $forum) {
        if (preg_match($expression, $forum['name'])) return $forum['id'];
      }
    }
  }
  
  public function get_forum_thread_id_by_title($forum_id, $title, $create = false) {
    if ($this->get_alliance_forum_threads($forum_id)) {
      if (is_array($this->threads[$forum_id])) foreach($this->threads[$forum_id]['data'] as $thread) {
        if ($thread['title'] == trim($title)) return $thread['id'];
      }
    }
    if ($create) {
      return  $this->create_alliance_forum_thread($forum_id, $title, 'Init: ' . date('d.m.Y H:i:s'));
    } else return false;
  }
  
  public function get_forum_thread_id_by_exp($forum_id, $expression) {
    if ($this->get_alliance_forum_threads($forum_id)) {
      if (is_array($this->threads[$forum_id])) foreach($this->threads[$forum_id]['data'] as $thread) {
        if (preg_match($expression, $thread['title'])) return $thread['id'];
      }
    }
  }
  
  public function get_first_thread_post($forum_id, $thread_id) {
    return $this->get_thread_post_id_by_num($forum_id, $thread_id, 0);
  }
  
  public function get_last_thread_post_id($forum_id, $thread_id) {
    if ($this->get_alliance_forum_posts($forum_id, $thread_id)) {
      $posts = array_keys($this->posts[$forum_id][$thread_id]['data']); 
      return $this->posts[$forum_id][$thread_id]['data'][end($posts)]['post_id'];
    } else return false;
  }
  
  public function get_thread_post_id_by_num($forum_id, $thread_id, $offset = 0) {
    if ($this->get_alliance_forum_posts($forum_id, $thread_id)) {
      if ($this->get_thread_post_count($forum_id, $thread_id) >= $offset) {
        $posts = array_keys($this->posts[$forum_id][$thread_id]['data']); 
        return $this->posts[$forum_id][$thread_id]['data'][$posts[$offset]]['post_id'];
      }
    }
    return false;
  }
  
	public function get_thread_post_by_num($forum_id, $thread_id, $offset = 0) {
    if ($this->get_alliance_forum_posts($forum_id, $thread_id)) {
      if ($this->get_thread_post_count($forum_id, $thread_id) >= $offset) {
        $posts = array_keys($this->posts[$forum_id][$thread_id]['data']); 
        return $this->posts[$forum_id][$thread_id]['data'][$posts[$offset]]['post_id'];
      }
    }
    return false;
  }
  
  public function get_thread_post_count($forum_id, $thread_id) {
    return count($this->posts[$forum_id][$thread_id]['data']);
  }
  
  public function get_thread_count($forum_id) {
    return count($this->threads[$forum_id]['data']);
  }
  
  public function get_alliance_forums() {
    $this->doInfoForums();
    $forums = ($this->stack) ? @$this->stack : null;
    if(is_array($forums)) {
      $this->forums = $this->analyse_forums($forums);
      $this->note = $this->forums;
      $this->debug("LoU get info for forums");
      $this->notify();
      return true;
    }
    return false;
  }
	
	public function get_all_forums() {
    if ($this->get_alliance_forums()) {
			return $this->forums['data'];
		}
		return false;
  }
	
  public function get_alliance_forum_threads($forum_id) {
    $this->doInfoForumThreads($forum_id);
    $threads = ($this->stack) ? @$this->stack : null;
    if(is_array($threads)) {
      $this->threads[$forum_id] = $this->analyse_threads($threads);
      $this->note = $this->threads[$forum_id];
      $this->debug("LoU get info for forum ({$forum_id}) threads");
      $this->notify();
      return true;
    }
    return false;
  }
	
	public function get_all_forum_threads($forum_id) {
    if ($this->get_alliance_forum_threads($forum_id)) {
			return $this->threads[$forum_id]['data'];
		}
		return false;
  }
  
  public function mark_alliance_forum_threads_as_read($forum_id) {
    $this->doMarkForumThreadsAsRead($forum_id);
    $ok = ($this->stack) ? (bool) @$this->stack : false;
    if($ok === true) {
      $this->get_alliance_forums();
      return true;
    }
    return true;
  }
  
  public function create_alliance_forum_thread($forum_id, $title, $message) {
    $this->doCreateForumThread($forum_id, $title, $message);
    $ok = ($this->stack) ? (bool) @$this->stack : false;
    if($ok === true) {
      $this->debug("LoU create forum threat ({$forum_id}) {$title}");
      $this->get_alliance_forum_threads($forum_id);
      return $this->get_forum_thread_id_by_title($forum_id, $title);
    }
    return false;
  }
  
  public function get_alliance_forum_posts($forum_id, $thread_id) {
    $this->doInfoForumPosts($forum_id, $thread_id);
    $posts = ($this->stack) ? @$this->stack : null;
    if(is_array($posts)) {
      $this->posts[$forum_id][$thread_id] = $this->analyse_posts($posts);
      $this->note = $this->posts[$forum_id][$thread_id];
      $this->debug("LoU get info for forum/thread ({$forum_id}/{$thread_id}) posts");
      $this->notify();
      return true;
    }
    return false;
  }
	
	public function get_all_forum_posts($forum_id, $thread_id) {
    if ($this->get_alliance_forum_posts($forum_id, $thread_id)) {
			return $this->posts[$forum_id][$thread_id]['data'];
		}
		return false;
  }
  
  public function delete_alliance_forums($forum_ids) {
    if (!is_array($forum_ids)) $forum_ids = array($forum_ids);
    $this->doDeleteForums($forum_ids);
    $ok = ($this->stack) ? @$this->stack : null;
    if($ok == count($forum_ids)) {
      $this->debug("LoU delete forums: " . implode(', ', $forum_ids));
      foreach($forum_ids as $forum_id) unset($this->forums[$forum_id]);
      return true;
    }
    return false;
  }
  
  public function delete_alliance_forum_threads($forum_id, $thread_ids) {
    if (!is_array($thread_ids)) $thread_ids = array($thread_ids);
    $this->doDeleteForumThreads($forum_id, $thread_ids);
    $ok = ($this->stack) ? @$this->stack : null;
    if($ok == count($thread_ids)) {
      $this->debug("LoU delete forums threads: " . implode(', ', $thread_ids));
      foreach($thread_ids as $thread_id) unset($this->threads[$forum_id][$thread_id]);
      return true;
    }
    return false;
  }
  
  public function delete_alliance_forum_threads_post($forum_id, $thread_id, $post_ids) {
    if (!is_array($post_ids)) $post_ids = array($post_ids);
    $this->doDeleteForumThreadPosts($forum_id, $thread_id, $post_ids);
    $ok = ($this->stack) ? @$this->stack : null;
    if($ok == count($post_ids)) {
      $this->debug("LoU delete forums thread posts: " . implode(', ', $post_ids));
      foreach($post_ids as $post_id) unset($this->posts[$forum_id][$thread_id][$post_id]);
      return true;
    }
    return false;
  }
  
  public function create_alliance_forum($name) {
    $this->doCreateForum($name);
    $ok = ($this->stack) ? (bool) @$this->stack : false;
    if($ok === true) {
      $this->debug("LoU create forum: {$name}");
      $this->get_alliance_forums();
      return $this->get_forum_id_by_name($name);
    }
    return false;
  }
  
  public function edit_alliance_forum_thread($forum_id, $thread_id, $title) {
    $this->doEditForumThread($forum_id, $thread_id, $title);
    $ok = ($this->stack) ? (bool) @$this->stack : false;
    if($ok === true) {
      $this->debug("LoU edit forum thread ({$forum_id}) {$title}");
      $this->threads[$forum_id][$thread_id]['data']['title'] = $title;
      return true;
    }
    return false;
  }
  
  public function edit_alliance_forum_post($forum_id, $thread_id, $post_id, $message) {
    $this->doEditForumPost($forum_id, $thread_id, $post_id, $message);
    $ok = ($this->stack) ? (bool) @$this->stack : false;
    if($ok === true) {
      $this->debug("LoU edit forum thread post ({$thread_id}) {$post_id}");
      $this->posts[$forum_id][$thread_id][$post_id]['data']['message'] = $message;
      return true;
    }
    return false;
  }
  
  public function create_alliance_forum_post($forum_id, $thread_id, $message) {
    $this->doCreateForumPost($forum_id, $thread_id, $message);
    $ok = ($this->stack) ? (bool) @$this->stack : false;
    if($ok === true) {
      $this->debug("LoU create forum thread post ({$thread_id})");
      return true;
    }
    return false;
  }
  
  public function edit_alliance_forum($forum_id, $name, $roles) {
    $this->doEditForum($forum_id, $name, $roles);
    $ok = ($this->stack) ? (bool) @$this->stack : false;
    if($ok === true) {
      $this->debug("LoU edit forum: {$forum_id}");
      $this->forums[$forum_id]['data']['name'] = $name;
      return true;
    }
    return false;
  }
  
  private function doInfoForums() {
    $d = array(
      "session"   => $this->session
    );
    $this->get("GetAllianceForums", $d);
  }
  
  private function doInfoForumThreads($forum_id) {
    $d = array(
      "session"   => $this->session,
      "forumID"   => $forum_id
    );
    $this->get("GetAllianceForumThreads", $d);
  }
  
  private function doMarkForumThreadsAsRead($forum_id) {
    $d = array(
      "session"   => $this->session,
      "forumID"   => $forum_id
    );
    $this->post("MarkAllThreadsAsRead", $d);
  }
  
  private function doCreateForumThread($forum_id, $title, $message) {
    $d = array(
      "session"          => $this->session,
      "forumID"          => $forum_id,
      "threadTitle"      => $title,
      "firstPostMessage" => $message
    );
    $this->post("CreateAllianceForumThread", $d);
  }
  
  private function doInfoForumPosts($forum_id, $thread_id) {
    $d = array(
      "session"   => $this->session,
      "forumID"   => $forum_id,
      "threadID"  => $thread_id
    );
    $this->get("GetAllianceForumPosts", $d);
  }
  
  private function doDeleteForums($forum_ids) {
    $d = array(
      "session"   => $this->session,
      "forumIDs"  => $forum_ids
    );
    $this->post("DeleteAllianceForums", $d);
  }
  
  private function doDeleteForumThreads($forum_id, $thread_ids) {
    $d = array(
      "session"   => $this->session,
      "forumID"   => $forum_id,
      "threadIDs" => $thread_ids
    );
    $this->post("DeleteAllianceForumThreads", $d);
  }
  
  private function doDeleteForumThreadPosts($forum_id, $thread_id, $post_ids) {
    $d = array(
      "session"   => $this->session,
      "forumID"   => $forum_id,
      "threadID"  => $thread_id,
      "postIDs"   => $post_ids
    );
    $this->post("DeleteAllianceForumPosts", $d);
  }
  
  private function doCreateForum($name) {
    $d = array(
      "session"   => $this->session,
      "Title"     => $name
    );
    $this->post("CreateAllianceForum", $d);
  }
  
  private function doEditForum($forum_id, $name, $roles) {
    foreach($roles as $right) {
      $read[]   = $right['r'];
      $write[]  = $right['w'];
    }
    $d = array(
      "session"   => $this->session,
      "forumID"   => $forum_id,
      "newTitle"  => $name,
      "roleIDs"   => array_keys($roles),
      "readPermissions"   => $read,
      "writePermissions"  => $write
    );
    $this->post("EditAllianceForum", $d);
  }
  
  public function doEditForumThread($forum_id, $thread_id, $title) {
    $d = array(
      "session"          => $this->session,
      "forumID"          => $forum_id,
      "threadID"         => $thread_id,
      "newTitle"         => $title
    );
    $this->post("EditAllianceForumThread", $d);
  }
  
  public function doCreateForumPost($forum_id, $thread_id, $message) {
    $d = array(
      "session"          => $this->session,
      "forumID"          => $forum_id,
      "threadID"         => $thread_id,
      "postMessage"      => $message
    );
    $this->post("CreateAllianceForumPost", $d);
  }
  
  public function doEditForumPost($forum_id, $thread_id, $post_id, $message) {
    $d = array(
      "session"          => $this->session,
      "forumID"          => $forum_id,
      "threadID"         => $thread_id,
      "postID"           => $post_id,
      "newMessage"       => $message
    );
    $this->post("EditAllianceForumPost", $d);
  }
  
  private function analyse_forums($forums) {
	
    global $_GAMEDATA;
    foreach($forums as $data)
      $items[$data['fi']] = array(
        'id'            => $data['fi'],
        'name'          => (!empty($_GAMEDATA->translations['tnf:'.strtolower(substr($data['ft'], 1))])) ? $_GAMEDATA->translations['tnf:'.strtolower(substr($data['ft'], 1))] : $data['ft'],
        'updated'       => $data['hup'],
        'rights'        => Forum::prepare_rights($data['rw'])
      );
                      
    return array('type' => FORUM, 'data' => $items);
  }

  private function analyse_threads($threads) {
    
    foreach($threads as $data)
      $items[$data['ti']] = array(
        'author_id'        => $data['ai'],
        'author_name'      => $data['an'],
        'updated'          => $data['hup'],
        'forum_post'       => Forum::analyse_posts(array($data['fp'])),
        'posts'            => $data['pc'],
        'new_posts'        => $data['hup'],
        'last_post'        => Forum::analyse_posts(array($data['lp'])),
        'id'               => $data['ti'],
        'title'            => $data['tt']
      );
                       
    return array('type' => THREAD, 'data' => $items);
  }
  
  private function analyse_posts($posts) {
    foreach($posts as $data)
      $items[$data['pi']] = array(
        'post_id'     => $data['pi'],
        'author_id'   => $data['pli'],
        'author_name' => $data['pn'],
        'last_change' => floor($data['t']/1000),
        'updated'     => $data['up'],
        'message'     => $data['m']
      );
                       
    return array('type' => POST, 'data' => $items);
  }
  
  static function prepare_rights($rights) {
    $_rights = array();
    foreach($rights as $right) {
      $_rights[] = array($right['i']  => array(
                               'read'  => $right['r'],
                               'write' => $right['w']));
    }
    return $_rights;
  }
  
  public function __call($name, $args) {
    return call_user_func_array(array($this->lou, $name), $args);
    //return $this->lou->$name($args);
  }
  
  static function __callStatic($name, $args) {
    return call_user_func_array("Lou::$name", $args);
    //return Lou::$name($args);
  }
  
  public function __set($name, $val) {
    $this->lou->$name = $val;
  }
  
  public function __get($name) {
    return $this->lou->$name;
  }

}
?>