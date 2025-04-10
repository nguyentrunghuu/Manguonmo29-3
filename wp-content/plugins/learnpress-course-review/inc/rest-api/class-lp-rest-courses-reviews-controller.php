<?php

use LearnPress\Models\CourseModel;
use LearnPress\Models\UserModel;

if ( ! class_exists( 'LP_REST_Courses_Reviews_Controller' ) ) {
	class LP_REST_Courses_Reviews_Controller extends LP_Abstract_REST_Controller {
		/**
		 * LP_REST_Courses_Reviews_Controller constructor.
		 */
		public function __construct() {
			$this->namespace = 'lp/v1';
			$this->rest_base = 'review';
			parent::__construct();
		}

		public function register_routes() {
			$this->routes = array(
				'course/(?P<id>[\d]+)'                => array(
					array(
						'methods'             => WP_REST_Server::READABLE,
						'callback'            => array( $this, 'get_item_review' ),
						'permission_callback' => '__return_true',
						'args'                => array(
							'page'     => array(
								'description'       => esc_html__( 'Paged', 'learnpress-course-review' ),
								'type'              => 'integer',
								'sanitize_callback' => 'absint',
							),
							'per_page' => array(
								'description'       => esc_html__( 'Per page', 'learnpress-course-review' ),
								'type'              => 'integer',
								'sanitize_callback' => 'absint',
							),
						),
					),
				),
				'submit'                              => array(
					array(
						'methods'             => WP_REST_Server::CREATABLE,
						'callback'            => array( $this, 'submit_review' ),
						'permission_callback' => '__return_true',
						'args'                => array(
							'id'      => array(
								'description'       => esc_html__( 'Course ID', 'learnpress-course-review' ),
								'type'              => 'integer',
								'sanitize_callback' => 'absint',
							),
							'rate'    => array(
								'description'       => esc_html__( 'Rate', 'learnpress-course-review' ),
								'type'              => 'integer',
								'sanitize_callback' => 'absint',
							),
							'title'   => array(
								'description'       => esc_html__( 'Title', 'learnpress-course-review' ),
								'type'              => 'string',
								'sanitize_callback' => 'sanitize_text_field',
							),
							'content' => array(
								'description'       => esc_html__( 'Content', 'learnpress-course-review' ),
								'type'              => 'string',
								'sanitize_callback' => 'sanitize_text_field',
							),
						),
					),
				),
				'rating-comment/course/(?P<id>[\d]+)' => array(
					array(
						'methods'             => WP_REST_Server::READABLE,
						'callback'            => array( $this, 'load_rating_comments' ),
						'permission_callback' => '__return_true',
					),
				),
			);

			parent::register_routes();
		}

		public function get_item_review( $request ) {
			$course_id     = $request->get_param( 'id' );
			$paged         = ! empty( $request->get_param( 'page' ) ) ? absint( $request->get_param( 'page' ) ) : 1;
			$per_page      = ! empty( $request->get_param( 'per_page' ) ) ? absint( $request->get_param( 'per_page' ) ) : LP_ADDON_COURSE_REVIEW_PER_PAGE;
			$show_template = ! empty( $request->get_param( 'show_template' ) ) ? absint( $request->get_param( 'show_template' ) ) : 0;

			$response       = new LP_REST_Response();
			$response->data = new stdClass();

			try {
				if ( empty( $course_id ) ) {
					throw new Exception( esc_html__( 'No Course ID param.', 'learnpress-course-review' ) );
				}

				$course = CourseModel::find( $course_id, true );
				if ( ! $course ) {
					throw new Exception( esc_html__( 'Course not found.', 'learnpress-course-review' ) );
				}

				$user = UserModel::find( get_current_user_id(), true );
				if ( ! $user ) {
					throw new Exception( esc_html__( 'User not found.', 'learnpress-course-review' ) );
				}

				$course_rate = learn_press_get_course_rate( $course_id, false );

				$course_review = learn_press_get_course_review( $course_id, $paged, $per_page, true );

				if ( empty( $course_review['reviews'] ) ) {
					throw new Exception( esc_html__( 'No review found.', 'learnpress-course-review' ) );
				}

				$response->data->rated   = $course_rate['rated'] ?? 0;
				$response->data->total   = $course_rate['total'] ?? 0;
				$response->data->items   = $course_rate['items'] ?? array();
				$response->data->reviews = $course_review ?? array();

				//template show more
				$paged                      = ! empty( $course_review['paged'] ) ? absint( $course_review['paged'] ) : 1;
				$pages                      = ! empty( $course_review['pages'] ) ? absint( $course_review['pages'] ) : 1;
				$can_review = LP_Addon_Course_Review_Preload::$addon->check_user_can_review_course( $user, $course );
				$response->data->can_review = $can_review;

				//template show more
				if ( $show_template ) {
					ob_start();
					LP_Addon_Course_Review_Preload::$addon->get_template(
						'list-reviews.php',
						array(
							'reviews'       => $course_review['reviews'],
							'course_review' => $course_review,
							'course_id'     => $course_id,
							'paged'         => $paged,
							'pages'         => $pages,
						)
					);
					$response->data->template = ob_get_clean();
				}

				if ( ! $can_review ) {
					$review = learn_press_get_user_rate( $course_id, $user->get_id() );
					if ( $review && ! $review->comment_approved ) {
						$response->data->comment_approved = 0;

						$response->message = __(
							'You have already reviewed this course. It will be visible after it has been approved',
							'learnpress-course-review'
						);
					}
				}

				$response->status = 'success';
			} catch ( \Throwable $th ) {
				ob_end_clean();
				$response->message = $th->getMessage();
			}

			return rest_ensure_response( $response );
		}

		/**
		 * Submit review
		 *
		 * @param $request
		 *
		 * @return LP_REST_Response
		 */
		public function submit_review( $request ): LP_REST_Response {
			$response = new LP_REST_Response();

			try {
				$course_id = $request->get_param( 'id' );
				$rate      = $request->get_param( 'rate' );
				$title     = $request->get_param( 'title' );
				$content   = $request->get_param( 'content' );
				$user_id   = get_current_user_id();

				if ( empty( $course_id ) ) {
					throw new Exception( esc_html__( 'No Course ID param.', 'learnpress-course-review' ) );
				}

				if ( empty( $user_id ) ) {
					throw new Exception( esc_html__( 'No User.', 'learnpress-course-review' ) );
				}

				$course = CourseModel::find( $course_id, true );
				if ( ! $course ) {
					throw new Exception( esc_html__( 'Course not found.', 'learnpress-course-review' ) );
				}

				$user = UserModel::find( $user_id, true );
				if ( ! $user ) {
					throw new Exception( esc_html__( 'User not found.', 'learnpress-course-review' ) );
				}

				$can_review = LP_Addon_Course_Review_Preload::$addon->check_user_can_review_course( $user, $course );
				if ( ! $can_review ) {
					throw new Exception( esc_html__( 'You can not submit review.', 'learnpress-course-review' ) );
				}

				$add_review = learn_press_add_course_review(
					array(
						'user_id'   => $user_id,
						'course_id' => $course_id,
						'rate'      => ! empty( $rate ) ? $rate : 0,
						'title'     => ! empty( $title ) ? $title : '',
						'content'   => ! empty( $content ) ? $content : '',
						'force'     => true, // Not use cache.
					)
				);

				if ( ! $add_review instanceof WP_Error ) {
					$response->data->comment_id = $add_review;
					$response->message          = is_admin() ? esc_html__( 'Your review submitted successfully', 'learnpress-course-review' ) : esc_html__( 'Thank you for your review. Your review will be visible after it has been approved', 'learnpress-course-review' );
					$response->status           = 'success';
				} else {
					throw new Exception( $add_review->get_error_message() );
				}
			} catch ( Throwable $th ) {
				$response->message = $th->getMessage();
			}

			return $response;
		}

		/**
		 * Load template course review on the single course
		 *
		 * @param WP_REST_Request $request
		 *
		 * @return LP_REST_Response
		 * @author minhpd
		 * @since 4.2.3
		 * @version 1.0.0
		 */
		public function load_rating_comments( WP_REST_Request $request ): LP_REST_Response {
			$response       = new LP_REST_Response();
			$response->data = '';

			try {
				$params    = $request->get_params();
				$course_id = absint( $params['id'] ?? 0 );
				if ( empty( $course_id ) ) {
					throw new Exception( esc_html__( 'Course is invalid!', 'learnpress-course-review' ) );
				}

				$user              = learn_press_get_current_user();
				$course_rate_res   = learn_press_get_course_rate( $course_id, false );
				$course_review     = learn_press_get_course_review( $course_id, 1 );
				$data_for_template = compact( 'course_id', 'user', 'course_rate_res', 'course_review' );

				ob_start();
				$data_for_template = apply_filters( 'lp/shortcode/course-review/data', $data_for_template );
				LP_Addon_Course_Review_Preload::$addon->get_template(
					'list-rating-reviews.php',
					[ 'data' => $data_for_template ]
				);

				$response->data   = ob_get_clean();
				$response->status = 'success';
			} catch ( \Throwable $th ) {
				ob_end_clean();
				$response->message = $th->getMessage();
			}

			return $response;
		}
	}
}
