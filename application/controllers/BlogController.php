<?php
defined('BASEPATH') or exit('No direct script access allowed');

class BlogController extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->library('Ajax_pagination');
    }

    public function blogDetails(){
        $data['blog_slug'] = $blog_slug = $this->uri->segment(2);
        $blog_arry = array('url_key'=>$blog_slug);
        $blogDetails = CommonRepository::blog_detail($blog_arry);   
        if (isset($blogDetails->statusCode) && $blogDetails->statusCode == '200') {
            $data['blogBredcrumb'] = $blogDetails->blogData->title;
            $data['MainBlogDescription'] = $blogDetails->blogData->description;
            $data['blogDetails'] = $blogDetails;

            $blog_id = $blogDetails->blogData->id;
            $resultArr = SearchRepository::get_blog_nextpre_blog($blog_id);
                    if (!empty($resultArr) && isset($resultArr) && $resultArr->statusCode == 200) {
                        $SearchDetails = $resultArr->BlogPrevNexList;
                        $prev = isset($SearchDetails->prev_arr) ? $SearchDetails->prev_arr : false;
                        $next = isset($SearchDetails->next_arr) ? $SearchDetails->next_arr : false;
                        if ($prev) {
                            $data['prev_url'] = $prev_url = base_url('blogs/'.$prev->url_key);
                        }
                        if ($next) {
                            $data['next_url'] = $next_url = base_url('blogs/'.$next->url_key);
                        }
                    }


        }
        $this->template->load('blog/blog_details', $data);     
    }

    public function index() {
        $page = 1;
        $show_limit = 3;
        $offset = ($page - 1) * $show_limit;

        $blog_arry = array('page' => $offset, 'page_size' => $show_limit);
        $blogCount = 0;
        $data['main_blog_list'] = $main_blog_list = CommonRepository::get_blog_listing($blog_arry);

        if (isset($main_blog_list->statusCode) && $main_blog_list->statusCode == '200') {
            $blogCount = $main_blog_list->BlogDataCount;
        }

        // Pagination config
        $config['target'] = '#product-list-section';
        $config['base_url'] = BASE_URL . 'BlogController/sort_by';
        $config['total_rows'] = $blogCount;
        $config['per_page'] = $show_limit;
        $config['cur_page'] = $page;
        $config['link_func'] = 'sort_by';

        $this->ajax_pagination->initialize($config);
        $data['PaginationLink'] = $this->ajax_pagination->create_links();

        $this->template->load('blog/blog_listing.php', $data);
    }


    public function sort_by()
    {
        if (!empty($_POST)) {
            $page = 3;
            $page_size = 3;

            $offset = 3;

            $blog_arry = array(
                'page' => $offset,
                'page_size' => $page_size
            );
            $data['main_blog_list'] = $main_blog_list = CommonRepository::get_blog_listing($blog_arry);

            if (isset($main_blog_list->statusCode) && $main_blog_list->statusCode == '200') {
                $blogCount = $main_blog_list->BlogDataCount;
            }

            // Pagination config
            $config['target'] = '#product-list-section';
            $config['base_url'] = BASE_URL . 'BlogController/sort_by';
            $config['total_rows'] = $blogCount;
            $config['per_page'] = $page_size;
            $config['cur_page'] = $page;
            $config['link_func'] = 'sort_by';

            $this->ajax_pagination->initialize($config);
            $data['PaginationLink'] = $this->ajax_pagination->create_links();

            // Load only partial view to return via AJAX
            $this->load->view('blog/blog_listing_sort_by', $data);
        }
    }





}


?>