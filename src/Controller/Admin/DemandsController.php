<?php
declare(strict_types=1);

namespace App\Controller\Admin;

use App\Controller\AppController;

/**
 * Demands Controller
 *
 * @property \App\Model\Table\DemandsTable $Demands
 * @method \App\Model\Entity\Demand[]|\Cake\Datasource\ResultSetInterface paginate($object = null, array $settings = [])
 */
class DemandsController extends AppController
{
    public function beforeFilter(\Cake\Event\EventInterface $event)
    {
        $this->Authorization->skipAuthorization();
        parent::beforeFilter($event);
    }
    /**
     * Index method
     *
     * @return \Cake\Http\Response|null|void Renders view
     */
    public function index()
    {
        /**
         * Cas de figure : 
         *  
         *   Manager:
         * - Les employés de mon département qui veulent une augmentation $raises
         * - Les employés de mon département qui veulent être muté ailleurs $incomers
         * - Les employés d'un autre département qui veulent être muté chez moi. $outcomers
         * 
         *   Comptable:
         * - Les employés qui veulent une augmentation.
         * 
         */

        $raises = $incomers = $leaving = [];

        $this->loadModel('Employees');
        
        $user=$this->Authentication->getIdentity();
        $user = $this->Employees->get($user->get('emp_no'), [
            'contain' => ['departments']
        ]);
        $userDept = $user->departments[0]->dept_no;
        $demands = $this->Demands->find()->toArray();
        foreach($demands as $demand){
            if($demand->status === 'pending'){
                if($demand->type === 'Raise'){
                    
                    $demandEmployee = $this->Employees->get($demand->emp_no, ['contain' => ['departments', 'titles']]);
                    if($_SESSION['status'] === 'Admin'
                        || $_SESSION['status'] === 'Accountant'
                        || $userDept === $demandEmployee->departments[0]->dept_no){
                        $demandEmployeeSalary = $this->Employees->get($demand->emp_no, ['contain' => ['salaries']])->salaries[0]->salary;
                       // dd($demandEmployeeSalary);
                        $demand->about = $demand->about;
                        $demand->currentSalary = $demandEmployeeSalary;
                        $demand->employee = $demandEmployee->first_name . ' ' . $demandEmployee->last_name;
                        $demand->employeeDepartment = $demandEmployee->departments[0]->dept_name;
                        $demand->employeeTitle = $demandEmployee->titles[0]->title;

                        $raises[] = $demand;
                    }
                }
                else {
                    $demandEmployee = $this->Employees->get($demand->emp_no, ['contain' => ['departments', 'titles']]);
                    $demandDept = $this->Employees->departments->get($demand->about);
                    $demand->employee = $demandEmployee->first_name . ' ' . $demandEmployee->last_name;
                    $demand->employeeTitle = $demandEmployee->titles[0]->title;

                    if($demand->about === $userDept){
                        $demand->employeeDepartment = $demandEmployee->departments[0]->dept_name;
                        $incomers[] = $demand;
                    }else{
                        $demand->department = $demandDept->dept_name;

                        $demandEmployeeDept = $demandEmployee->departments[0]->dept_no;
                        if($userDept===$demandEmployeeDept || $_SESSION['status'] === 'Admin'){
                            $leaving[] = $demand;
                        }
                    }
                }
            }
        }
        $this->set(compact('incomers', 'raises', 'leaving'));
    }

    /**
     * View method
     *
     * @param string|null $id Demand id.
     * @return \Cake\Http\Response|null|void Renders view
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function view($id = null)
    {
        $demand = $this->Demands->get($id, [
            'contain' => [],
        ]);
        $this->set(compact('demand'));
    }

    /**
     * Add method
     *
     * @return \Cake\Http\Response|null|void Redirects on successful add, renders view otherwise.
     */
    public function add()
    {
        $demand = $this->Demands->newEmptyEntity();
        if ($this->request->is('post')) {
            $demand = $this->Demands->patchEntity($demand, $this->request->getData());
            if ($this->Demands->save($demand)) {
                $this->Flash->success(__('The demand has been saved.'));

                return $this->redirect(['action' => 'index']);
            }
            $this->Flash->error(__('The demand could not be saved. Please, try again.'));
        }
        $this->set(compact('demand'));
    }

    /**
     * Edit method
     *
     * @param string|null $id Demand id.
     * @return \Cake\Http\Response|null|void Redirects on successful edit, renders view otherwise.
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function edit($id = null)
    {
        $demand = $this->Demands->get($id, [
            'contain' => [],
        ]);
        try{
            $this->Authorization->authorize($demand);
            if ($this->request->is(['patch', 'post', 'put'])) {
                $demand = $this->Demands->patchEntity($demand, $this->request->getData());
                if ($this->Demands->save($demand)) {
                    $this->Flash->success(__('The demand has been saved.'));
    
                    return $this->redirect(['action' => 'index']);
                }
                $this->Flash->error(__('The demand could not be saved. Please, try again.'));
            }
            $this->set(compact('demand'));
        }catch(\Exception $e){
            $this->Flash->error(__($e->getResult()->getReason()));
            return $this->redirect(['action' => 'index']);
        };
       
    }

    /**
     * Delete method
     *
     * @param string|null $id Demand id.
     * @return \Cake\Http\Response|null|void Redirects to index.
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function delete($id = null)
    {
        $this->request->allowMethod(['post', 'delete']);
        $demand = $this->Demands->get($id);
        if ($this->Demands->delete($demand)) {
            $this->Flash->success(__('The demand has been deleted.'));
        } else {
            $this->Flash->error(__('The demand could not be deleted. Please, try again.'));
        }

        return $this->redirect(['action' => 'index']);
    }
}
