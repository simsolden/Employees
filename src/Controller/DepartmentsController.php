<?php
declare(strict_types=1);

namespace App\Controller;
use \DateTime;

/**
 * Departments Controller
 *
 * @property \App\Model\Table\DepartmentsTable $Departments
 * @method \App\Model\Entity\Department[]|\Cake\Datasource\ResultSetInterface paginate($object = null, array $settings = [])
 */
class DepartmentsController extends AppController
{
    /**
     * Index method
     *
     * @return \Cake\Http\Response|null|void Renders view
     */
    public function index()
    {   
        $this->Authorization->skipAuthorization();

        $departments = $this->paginate($this->Departments);

        $this->set(compact('departments'));

    }

    /**
     * View method
     *
     * @param string|null $id Department id.
     * @return \Cake\Http\Response|null|void Renders view
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function view($id = null)
    {
       $emp = $this->Authentication->getIdentity()->get('emp_no');
        $employees = $this->getTableLocator()->get('Employees')->
             get($emp, [
            'contain' => ['departments'],
        ]); 
        $dept_no = $employees->departments[0]->dept_no;
        
        $showRoi = $dept_no === $id; 
        
        $department = $this->Departments->get($id, [
            'contain' => ['Managers', 'Vacancies'],
        ]);
        
        $this->Authorization->authorize($department);

        $managers =$department->managers;
        $department->manager = $managers[0]->picture;
        // $today = new DateTime();
        // foreach($managers as $manager) {
        //    $date = new DateTime($manager['_joinData']->to_date->format('Y-m-d'));
        //    // dd($department->manager);
        //     if($date > $today) {
        //         $department->manager = $manager->picture;
        //         //dd($manager->picture);
        //        // dd( $department->managers);

        //         break;
        //     }
        // }
        
        $result = $this->Departments->find('count', ['id' => $id])->first()->count;

        //Récupérer les liens des photos (de la BDD) de chaque manager pour le département correspondant
        // $dep = $this->getTableLocator()->get('Departments');
        // $query = $dep->find();

        //     $query->select([
        //         'Employees.emp_no',
        //         'count' => $query->func()->count('*')]);
        //     $query->innerJoinWith('Employees')
        //     ->where(['departments.dept_no =' => $id]);

        // $result = $query->first()->count;
        
        
        //Récupérer les RULES de la BDD              ----------------------------->pourquoi pas de foreach ????? pourquoi cela récupère direct le bon fichier ?
        $rules = $department->rules;
      
        
        //Récupérer la description de la BDD
        $description = $department->description;
        
        //Nombre de postes vacants pour chaque département
       $vacancies = $this->getTableLocator()->get('Vacancies');
        //dd($vacancies);
   
        $query = $vacancies->find();
        $query->select(['quantity' => $query -> func()->sum('quantity'), 'deptNo' => 'dept_no'])
                ->where(['dept_no' => $department->dept_no])
                ->group('dept_no');
        
     
        $nbVacancies = $query->all();
        //dd($nbVacancies);

        foreach($nbVacancies as $nbVacancie):
            $department->vacancie = $nbVacancie->quantity;
        endforeach;
            //dd($department->vacancie);
          
        //Envoyer à la vue
        $this->set(compact('department', 'result', 'rules', 'description', 'showRoi'));
    }

    /**
     * Add method
     *
     * @return \Cake\Http\Response|null|void Redirects on successful add, renders view otherwise.
     */
    public function add()
    {
        $department = $this->Departments->newEmptyEntity();
        if ($this->request->is('post')) {
            $department = $this->Departments->patchEntity($department, $this->request->getData());
            if ($this->Departments->save($department)) {
                $this->Flash->success(__('The department has been saved.'));

                return $this->redirect(['action' => 'index']);
            }
            $this->Flash->error(__('The department could not be saved. Please, try again.'));
        }
        $this->set(compact('department'));
    }

    /**
     * Edit method
     *
     * @param string|null $id Department id.
     * @return \Cake\Http\Response|null|void Redirects on successful edit, renders view otherwise.
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function edit($id = null)
    {
        $department = $this->Departments->get($id, [
            'contain' => [],
        ]);
        if ($this->request->is(['patch', 'post', 'put'])) {
            $department = $this->Departments->patchEntity($department, $this->request->getData());
            if ($this->Departments->save($department)) {
                $this->Flash->success(__('The department has been saved.'));

                return $this->redirect(['action' => 'index']);
            }
            $this->Flash->error(__('The department could not be saved. Please, try again.'));
        }
        $this->set(compact('department'));
    }

    /**
     * Delete method
     *
     * @param string|null $id Department id.
     * @return \Cake\Http\Response|null|void Redirects to index.
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function delete($id = null)
    {
        $this->request->allowMethod(['post', 'delete']);
        $department = $this->Departments->get($id);
        if ($this->Departments->delete($department)) {
            $this->Flash->success(__('The department has been deleted.'));
        } else {
            $this->Flash->error(__('The department could not be deleted. Please, try again.'));
        }

        return $this->redirect(['action' => 'index']);
    }
}
