<?php
/* For licensing terms, see /license.txt */

use ChamiloSession as Session;
use \Doctrine\Common\Collections\Criteria;

/**
 * Class Evaluation
 * @package chamilo.gradebook
 */
class Evaluation implements GradebookItem
{
	private $id;
	private $name;
	private $description;
	private $user_id;
	private $course_code;
	/** @var Category */
	private $category;
	private $created_at;
	private $weight;
	private $eval_max;
	private $visible;
	private $sessionId;
	public $studentList;

	/**
	 * Construct
	 */
	public function __construct()
	{
	}

	/**
	 * @return Category
	 */
	public function getCategory()
	{
		return $this->category;
	}

	/**
	 * @param Category $category
	 */
	public function setCategory($category)
	{
		$this->category = $category;
	}

	/**
	 * @return int
	 */
	public function get_category_id()
	{
		return $this->category->get_id();
	}

	/**
	 * @param int $category_id
	 */
	public function set_category_id($category_id)
	{
		$categories = Category::load($category_id);
		if (isset($categories[0])) {
			$this->setCategory($categories[0]);
		}
	}

	/**
	 * @return int
	 */
	public function get_id()
	{
		return $this->id;
	}

	/**
	 * @return string
	 */
	public function get_name()
	{
		return $this->name;
	}

	public function get_description()
	{
		return $this->description;
	}

	public function get_user_id()
	{
		return $this->user_id;
	}

	public function get_course_code()
	{
		return $this->course_code;
	}

	/**
	 * @return int
	 */
	public function getSessionId()
	{
		return $this->sessionId;
	}

	/**
	 * @param int $sessionId
	 */
	public function setSessionId($sessionId)
	{
		$this->sessionId = intval($sessionId);
	}

	public function get_date()
	{
		return $this->created_at;
	}

	public function get_weight()
	{
		return $this->weight;
	}

	public function get_max()
	{
		return $this->eval_max;
	}

	public function get_type()
	{
		return $this->type;
	}

	public function is_visible()
	{
		return $this->visible;
	}

	public function get_locked()
	{
		return $this->locked;
	}

	public function is_locked()
	{
		return isset($this->locked) && $this->locked == 1 ? true : false;
	}

	public function set_id($id)
	{
		$this->id = $id;
	}

	public function set_name($name)
	{
		$this->name = $name;
	}

	public function set_description($description)
	{
		$this->description = $description;
	}

	public function set_user_id($user_id)
	{
		$this->user_id = $user_id;
	}

	public function set_course_code($course_code)
	{
		$this->course_code = $course_code;
	}

	public function set_date($date)
	{
		$this->created_at = $date;
	}

	public function set_weight($weight)
	{
		$this->weight = $weight;
	}

	public function set_max($max)
	{
		$this->eval_max = $max;
	}

	public function set_visible($visible)
	{
		$this->visible = $visible;
	}

	public function set_type($type)
	{
		$this->type = $type;
	}

	public function set_locked($locked)
	{
		$this->locked = $locked;
	}

	/**
	 * Retrieve evaluations and return them as an array of Evaluation objects
	 * @param int $id evaluation id
	 * @param int $user_id user id (evaluation owner)
	 * @param string $course_code course code
	 * @param int $category_id parent category
	 * @param $visible visible
	 */
	public static function load(
		$id = null,
		$user_id = null,
		$course_code = null,
		$category_id = null,
		$visible = null,
		$locked = null
	) {
        $em = Database::getManager();
        $criteria = Criteria::create();

		if (isset ($id)) {
            $criteria->andWhere(
                Criteria::expr()->eq('id', $id)
            );
		}

		if (isset ($user_id)) {
            $user_id = intval($user_id);
            $criteria->andWhere(
                Criteria::expr()->eq('userId', $user_id)
            );
		}

		if (isset ($course_code) && $course_code <> '-1') {
            $courseId = api_get_course_int_id($course_code);
            $criteria->andWhere(
                Criteria::expr()->eq('course', $courseId)
            );
		}

		if (isset ($category_id)) {
            $category_id = intval($category_id);
            $criteria->andWhere(
                Criteria::expr()->eq('categoryId', $category_id)
            );
		}

		if (isset ($visible)) {
            $visible = intval($visible);
            $criteria->andWhere(
                Criteria::expr()->eq('visible', $visible)
            );
		}

		if (isset ($locked)) {
            $locked = intval($locked);
            $criteria->andWhere(
                Criteria::expr()->eq('locked', $locked)
            );
		}

		$result = $em->getRepository('ChamiloCoreBundle:GradebookEvaluation')->matching($criteria);
        $alleval = Evaluation::createEvaluationObjectsFromEntities($result);

		return $alleval;
	}

    /**
     * Get an Evaluation array from an \Chamilo\CoreBundle\Entity\GradebookEvaluation collection
     * @param \Doctrine\Common\Collections\ArrayCollection|array $entities
     * @return array
     */
    private static function createEvaluationObjectsFromEntities($entities)
    {
        $alleval = array();

        foreach ($entities as $gradebookEvaluation) {
            $eval= new Evaluation();
            $eval->set_id($gradebookEvaluation->getId());
            $eval->set_name($gradebookEvaluation->getName());
            $eval->set_description($gradebookEvaluation->getDescription());
            $eval->set_user_id($gradebookEvaluation->getUserId());
            $eval->set_course_code($gradebookEvaluation->getCourse()->getCode());
            $eval->set_category_id($gradebookEvaluation->getCategoryId());
            $eval->set_date(api_get_local_time($gradebookEvaluation->getCreatedAt()));
            $eval->set_weight($gradebookEvaluation->getWeight());
            $eval->set_max($gradebookEvaluation->getMax());
            $eval->set_visible($gradebookEvaluation->getVisible());
            $eval->set_type($gradebookEvaluation->getType());
            $eval->set_locked($gradebookEvaluation->getLocked());
            $eval->setSessionId(api_get_session_id());

            $alleval[] = $eval;
        }

        return $alleval;
    }

	/**
	 * @param array $result
	 * @return array
	 */
	private static function create_evaluation_objects_from_sql_result($result)
	{
		$alleval = array();
		if (Database::num_rows($result)) {
			while ($data = Database::fetch_array($result)) {
				$eval= new Evaluation();
				$eval->set_id($data['id']);
				$eval->set_name($data['name']);
				$eval->set_description($data['description']);
				$eval->set_user_id($data['user_id']);
				$eval->set_course_code($data['course_code']);
				$eval->set_category_id($data['category_id']);
				$eval->set_date(api_get_local_time($data['created_at']));
				$eval->set_weight($data['weight']);
				$eval->set_max($data['max']);
				$eval->set_visible($data['visible']);
				$eval->set_type($data['type']);
				$eval->set_locked($data['locked']);
				$eval->setSessionId(api_get_session_id());

				$alleval[] = $eval;
			}
		}

		return $alleval;
	}

	/**
	 * Insert this evaluation into the database
	 */
	public function add()
	{
		if (isset($this->name) &&
			isset($this->user_id) &&
			isset($this->weight) &&
			isset ($this->eval_max) &&
			isset($this->visible)
		) {
            $em = Database::getManager();

            $createdAt = new DateTime(api_get_utc_datetime(), 'UTC');

            $gradebookEvaluation = new \Chamilo\CoreBundle\Entity\GradebookEvaluation();
            $gradebookEvaluation
                ->setName($this->get_name())
                ->setUserId($this->get_user_id())
                ->setWeight($this->get_weight())
                ->setMax($this->get_max())
                ->setVisible($this->is_visible())
                ->setCreatedAt($createdAt);

			if (isset($this->description)) {
                $gradebookEvaluation->setDescription($this->get_description());
			}
			if (isset($this->course_code)) {
                $course = $em->getRepository('ChamiloCoreBundle:Course')->findOneBy([
                    'code' => $this->get_course_code()
                ]);
                $gradebookEvaluation->setCourse($course);
			}
			if (isset($this->category)) {
                $gradebookEvaluation->setCategoryId($this->get_category_id());
			}
			if (empty($this->type)) {
				$this->type = 'evaluation';
                $gradebookEvaluation->setType($this->get_type());
			}

            $em->persist($gradebookEvaluation);
            $em->flush();

			$this->set_id($gradebookEvaluation->getId());
		} else {
			die('Error in Evaluation add: required field empty');
		}
	}

	/**
	 * @param int $idevaluation
	 */
	public function add_evaluation_log($idevaluation)
	{
		if (!empty($idevaluation)) {
            $em = Database::getManager();

			$tbl_grade_linkeval_log = Database :: get_main_table(TABLE_MAIN_GRADEBOOK_LINKEVAL_LOG);
			$eval = new Evaluation();
			$dateobject = $eval->load($idevaluation,null,null,null,null);
			$arreval = get_object_vars($dateobject[0]);
			if (!empty($arreval['id'])) {
                $row_old_weight = $em->find('ChamiloCoreBundle:GradebookEvaluation', $arreval['id']);
                $current_date = api_get_utc_datetime();
                $params = [
                    'id_linkeval_log' => $arreval['id'],
                    'name' => $arreval['name'],
                    'description' => $arreval['description'],
                    'created_at' => $current_date,
                    'weight' => $row_old_weight->getWeight(),
                    'visible' => $arreval['visible'],
                    'type' => 'evaluation',
                    'user_id_log' => api_get_user_id()
                ];
                Database::insert($tbl_grade_linkeval_log, $params);
			}
		}
	}

	/**
	 * Update the properties of this evaluation in the database
	 */
	public function save()
	{
        $em = Database::getManager();
        $gradebookEvaluation = $em->find('ChamiloCoreBundle:GradebookEvaluation', $this->id);

        if ($gradebookEvaluation) {
            return;
        }

        $gradebookEvaluation->setName($this->get_name())
            ->setUserId($this->get_user_id())
            ->setWeight($this->get_weight())
            ->setMax($this->get_max())
            ->setVisible($this->is_visible());

		if (isset($this->description)) {
            $gradebookEvaluation->setDescription($this->get_description());
		}

		if (isset($this->course_code)) {
            $course = $em->getRepository('ChamiloCoreBundle:Course')->findOneBy([
                'code' => $this->get_course_code()
            ]);
            $gradebookEvaluation->setCourse($course);
		}

		if (isset($this->category)) {
            $gradebookEvaluation->setCategoryId($this->get_category_id());
		}
		//recorded history

		$eval_log = new Evaluation();
		$eval_log->add_evaluation_log($this->id);

        $em->persist($gradebookEvaluation);
        $em->flush();
	}

	/**
	 * Delete this evaluation from the database
	 */
	public function delete()
	{
        $em = Database::getManager();
        $gradebookEvaluation = $em->find('ChamiloCoreBundle:GradebookEvaluation', $this->id);

        $em->remove($gradebookEvaluation);
        $em->flush();
	}

	/**
	 * Check if an evaluation name (with the same parent category) already exists
	 * @param $name name to check (if not given, the name property of this object will be checked)
	 * @param $parent parent category
	 */
	public function does_name_exist($name, $parent)
	{
		if (!isset ($name)) {
			$name = $this->name;
			$parent = $this->category;
		}
		$tbl_grade_evaluations = Database :: get_main_table(TABLE_MAIN_GRADEBOOK_EVALUATION);
		$sql = 'SELECT count(id) AS number'
			.' FROM '.$tbl_grade_evaluations
			." WHERE name = '".Database::escape_string($name)."'";

		if (api_is_allowed_to_edit()) {
			$parent = Category::load($parent);
			$code = $parent[0]->get_course_code();
			$courseInfo = api_get_course_info($code);
			$courseId = $courseInfo['real_id'];

			if (isset($code) && $code != '0') {
				$main_course_user_table = Database :: get_main_table(TABLE_MAIN_COURSE_USER);
				$sql .= ' AND user_id IN (
					 SELECT user_id FROM '.$main_course_user_table.'
					 WHERE
						c_id = '.$courseId.' AND
						status = '.COURSEMANAGER.'
					)';
			} else {
				$sql .= ' AND user_id = '.api_get_user_id();
			}

		}else {
			$sql .= ' AND user_id = '.api_get_user_id();
		}

		if (!isset ($parent)) {
			$sql.= ' AND category_id is null';
		} else {
			$sql.= ' AND category_id = '.intval($parent);
		}
		$result = Database::query($sql);
		$number=Database::fetch_row($result);

		return $number[0] != 0;
	}

	/**
	 * Are there any results for this evaluation yet ?
	 * The 'max' property should not be changed then.
	 */
	public function has_results()
	{
		$tbl_grade_results = Database :: get_main_table(TABLE_MAIN_GRADEBOOK_RESULT);
		$sql = 'SELECT count(id) AS number
				FROM '.$tbl_grade_results.'
				WHERE evaluation_id = '.intval($this->id);
		$result = Database::query($sql);
		$number=Database::fetch_row($result);

		return ($number[0] != 0);
	}

	/**
	 * Delete all results for this evaluation
	 */
	public function delete_results()
	{
		$tbl_grade_results = Database :: get_main_table(TABLE_MAIN_GRADEBOOK_RESULT);
		$sql = 'DELETE FROM '.$tbl_grade_results.'
				WHERE evaluation_id = '.intval($this->id);
		Database::query($sql);
	}

	/**
	 * Delete this evaluation and all underlying results.
	 */
	public function delete_with_results()
	{
		$this->delete_results();
		$this->delete();
	}

	/**
	 * Check if the given score is possible for this evaluation
	 */
	public function is_valid_score($score)
	{
		return is_numeric($score) && $score >= 0 && $score <= $this->eval_max;
	}

	/**
	 * Calculate the score of this evaluation
	 * @param int $stud_id (default: all students who have results for this eval - then the average is returned)
	 * @param string $type (best, average, ranking)
	 * @return	array (score, max) if student is given
	 * 			array (sum of scores, number of scores) otherwise
	 * 			or null if no scores available
	 */
	public function calc_score($stud_id = null, $type = null)
	{
        $useSession = true;
		if (isset($stud_id) && empty($type)) {
			$key = 'result_score_student_list_'.api_get_course_int_id().'_'.api_get_session_id().'_'.$this->id.'_'.$stud_id;
			$data = Session::read('calc_score');
            $results = isset($data[$key]) ? $data[$key] : null;

            if ($useSession == false) {
                $results  = null;
            }
			if (empty($results)) {
				$results = Result::load(null, $stud_id, $this->id);
				Session::write('calc_score', array($key => $results));
			}

			$score = 0;
			/** @var Result $res */
			foreach ($results as $res) {
				$score = $res->get_score();
			}

			return array($score, $this->get_max());
		} else {

			$count = 0;
			$sum = 0;
			$bestResult = 0;
			$weight = 0;
			$sumResult = 0;

			$key = 'result_score_student_list_'.api_get_course_int_id().'_'.api_get_session_id().'_'.$this->id;
            $data = Session::read('calc_score');
            $allResults = isset($data[$key]) ? $data[$key] : null;
            if ($useSession == false) {
                $allResults  = null;
            }
			if (empty($allResults)) {
				$allResults = Result::load(null, null, $this->id);
				Session::write($key, $allResults);
			}

			$students = array();
			/** @var Result $res */
			foreach ($allResults as $res) {
				$score = $res->get_score();
				if (!empty($score) || $score == '0') {
					$count++;
					$sum += $score / $this->get_max();
					$sumResult += $score;
					if ($score > $bestResult) {
						$bestResult = $score;
					}
					$weight = $this->get_max();
				}
				$students[$res->get_user_id()] = $score;
			}

			if (empty($count)) {
				return null;
			}

			switch ($type) {
				case 'best':
					return array($bestResult, $weight);
					break;
				case 'average':
					return array($sumResult/$count, $weight);
					break;
				case 'ranking':
                    $students = array();
                    /** @var Result $res */
                    foreach ($allResults as $res) {
                        $score = $res->get_score();
                        $students[$res->get_user_id()] = $score;
                    }
					return AbstractLink::getCurrentUserRanking($stud_id, $students);
					break;
				default:
					return array($sum, $count);
					break;
			}
		}
	}

	/**
	 * Generate an array of possible categories where this evaluation can be moved to.
	 * Notice: its own parent will be included in the list: it's up to the frontend
	 * to disable this element.
	 * @return array 2-dimensional array - every element contains 3 subelements (id, name, level)
	 */
	public function get_target_categories()
	{
		// - course independent evaluation
		//   -> movable to root or other course independent categories
		// - evaluation inside a course
		//   -> movable to root, independent categories or categories inside the course
		$user = (api_is_platform_admin() ? null : api_get_user_id());
		$targets = array();
		$level = 0;

		$root = array(0, get_lang('RootCat'), $level);
		$targets[] = $root;

		if (isset($this->course_code) && !empty($this->course_code)) {
			$crscats = Category::load(null,null,$this->course_code,0);
			foreach ($crscats as $cat) {
				$targets[] = array ($cat->get_id(), $cat->get_name(), $level+1);
				$targets = $this->add_target_subcategories($targets, $level+1, $cat->get_id());
			}
		}

		$indcats = Category::load(null,$user,0,0);
		foreach ($indcats as $cat) {
			$targets[] = array ($cat->get_id(), $cat->get_name(), $level+1);
			$targets = $this->add_target_subcategories($targets, $level+1, $cat->get_id());
		}

		return $targets;
	}

	/**
	 * Internal function used by get_target_categories()
	 */
	private function add_target_subcategories($targets, $level, $catid)
	{
		$subcats = Category::load(null,null,null,$catid);
		foreach ($subcats as $cat) {
			$targets[] = array ($cat->get_id(), $cat->get_name(), $level+1);
			$targets = $this->add_target_subcategories($targets, $level+1, $cat->get_id());
		}
		return $targets;
	}

	/**
	 * Move this evaluation to the given category.
	 * If this evaluation moves from inside a course to outside,
	 * its course code is also changed.
	 */
	public function move_to_cat($cat)
	{
		$this->set_category_id($cat->get_id());
		if ($this->get_course_code() != $cat->get_course_code()) {
			$this->set_course_code($cat->get_course_code());
		}
		$this->save();
	}

	/**
	 * Retrieve evaluations where a student has results for
	 * and return them as an array of Evaluation objects
	 * @param int $cat_id parent category (use 'null' to retrieve them in all categories)
	 * @param int $stud_id student id
	 */
	public static function get_evaluations_with_result_for_student($cat_id = null, $stud_id)
	{
        $em = Database::getManager();
        $query = $em->createQuery();

        $dql = '
            SELECT ge FROM ChamiloCoreBundle:GradebookEvaluation ge
            WHERE gc IN (
                SELECT gr.evaluationId FROM ChamiloCoreBundle:GradebookResult gr
                WHERE gr.userId = :user AND gr.score IS NOT NULL
            )
        ';
        $queryParams = ['user' => intval($stud_id)];
		if (!api_is_allowed_to_edit()) {
            $dql .= 'AND ge.visible = 1 ';
		}
		if (isset($cat_id)) {
            $dql .= 'AND ge.categoryId = :category ';
            $queryParams['category'] = intval($cat_id);
		} else {
            $dql .= 'AND ge.categoryId >= 0 ';
		}

        $result = $query->setParameters($queryParams)->getResult();
        $alleval = Evaluation::createEvaluationObjectsFromEntities($result);

		return $alleval;
	}

	/**
	 * Get a list of students that do not have a result record for this evaluation
	 */
	public function get_not_subscribed_students($first_letter_user = '')
	{
		$tbl_user = Database :: get_main_table(TABLE_MAIN_USER);
		$tbl_grade_results = Database :: get_main_table(TABLE_MAIN_GRADEBOOK_RESULT);

		$sql = 'SELECT user_id,lastname,firstname,username FROM '.$tbl_user
			." WHERE lastname LIKE '".Database::escape_string($first_letter_user)."%'"
			.' AND status = '.STUDENT
			.' AND user_id NOT IN'
			.' (SELECT user_id FROM '.$tbl_grade_results
			.' WHERE evaluation_id = '.intval($this->id)
			.' )'
			.' ORDER BY lastname';

		$result = Database::query($sql);
		$users = Database::store_result($result);

		return $users;
	}

	/**
	 * Find evaluations by name
	 * @param string $name_mask search string
	 * @return array evaluation objects matching the search criterium
	 * @todo can be written more efficiently using a new (but very complex) sql query
	 */
	public function find_evaluations($name_mask,$selectcat)
	{
		$rootcat = Category::load($selectcat);
		$evals = $rootcat[0]->get_evaluations((api_is_allowed_to_create_course() ? null : api_get_user_id()), true);
		$foundevals = array();
		foreach ($evals as $eval) {
			if (!(api_strpos(api_strtolower($eval->get_name()), api_strtolower($name_mask)) === false)) {
				$foundevals[] = $eval;
			}
		}
		return $foundevals;
	}

	public function get_item_type()
	{
		return 'E';
	}

	public function get_icon_name()
	{
		return $this->has_results() ? 'evalnotempty' : 'evalempty';
	}

	/**
	 * Locks an evaluation, only one who can unlock it is the platform administrator.
	 * @param int locked 1 or unlocked 0
	 *
	 **/
	function lock($locked)
	{
        $em = Database::getManager();

        $gradebookEvaluation = $em->find('ChamiloCoreBundle:GradebookEvaluation', $this->id);

        if ($gradebookEvaluation) {
            $gradebookEvaluation->setLocked($locked);

            $em->persist($gradebookEvaluation);
            $em->flush();
        }
	}

	function check_lock_permissions()
	{
		if (api_is_platform_admin()) {
			return true;
		} else {
			if ($this->is_locked()) {
				api_not_allowed();
			}
		}
	}

	function delete_linked_data()
	{

	}

    public function getStudentList()
    {
        return $this->studentList;
    }

	public function setStudentList($list)
	{
		$this->studentList = $list;
	}
}
