<?php
declare(strict_types=1);

namespace App\Controller;

use App\Controller\AppController;

class CartItemsController extends AppController
{
    public function index()
    {
        $plantsTable = $this->getTableLocator()->get('Plants');
        $plants = $plantsTable->find('all')->toArray();

        $cartItems = $this->CartItems->find('all', [
            'contain' => ['Plants']
        ])->toArray();

        $this->set(compact('plants', 'cartItems'));
    }

    public function checkout()
    {
        $this->request->allowMethod(['post']);

        $this->CartItems->deleteAll([]);
        $this->Flash->success('Compra confirmada com sucesso!');

        return $this->redirect(['controller' => 'Plataforma', 'action' => 'index']);
    }

    public function add($plantId = null)
    {
        $this->request->allowMethod(['post', 'get']);

        $plantsTable = $this->getTableLocator()->get('Plants');
        $plant = $plantsTable->get($plantId);

        $existingItem = $this->CartItems->find()
            ->where(['plant_id' => $plantId])
            ->first();

        if ($existingItem) {
            return $this->redirect(['action' => 'increase', $existingItem->id]);
        }

        $cartItem = $this->CartItems->newEmptyEntity();
        $cartItem->plant_id = $plant->id;
        $cartItem->quantity = 1;

        if ($this->CartItems->save($cartItem)) {
            $plant->stock -= 1;
            $plantsTable->save($plant);

            $this->Flash->success(__('Planta adicionada ao carrinho com sucesso!'));
        } else {
            $this->Flash->error(__('Erro ao adicionar planta ao carrinho.'));
        }

        return $this->redirect($this->referer());
    }

    public function increase($id = null)
    {
        $plantsTable = $this->getTableLocator()->get('Plants');

        $cartItem = $this->CartItems->get($id, ['contain' => ['Plants']]);
        $plant = $cartItem->plant;

        if ($plant->stock > 0) {
            $cartItem->quantity += 1;
            if ($this->CartItems->save($cartItem)) {
                $plant->stock -= 1;
                $plantsTable->save($plant);
            }
        } else {
            $this->Flash->error('Estoque insuficiente para adicionar mais desta planta.');
        }

        return $this->redirect($this->referer());
    }

    public function decrease($id = null)
    {
        $plantsTable = $this->getTableLocator()->get('Plants');

        $cartItem = $this->CartItems->get($id, ['contain' => ['Plants']]);
        $plant = $cartItem->plant;

        if ($cartItem->quantity > 1) {
            $cartItem->quantity -= 1;
            if ($this->CartItems->save($cartItem)) {
                $plant->stock += 1;
                $plantsTable->save($plant);
            }
        } else {
            if ($this->CartItems->delete($cartItem)) {
                $plant->stock += 1;
                $plantsTable->save($plant);
            }
        }

        return $this->redirect($this->referer());
    }

    public function edit($id = null)
    {
        $plantsTable = $this->getTableLocator()->get('Plants');

        $cartItem = $this->CartItems->get($id, ['contain' => []]);
        if ($this->request->is(['patch', 'post', 'put'])) {
            $cartItem = $this->CartItems->patchEntity($cartItem, $this->request->getData());
            if ($this->CartItems->save($cartItem)) {
                $this->Flash->success(__('O item do carrinho foi salvo.'));
                return $this->redirect(['action' => 'index']);
            }
            $this->Flash->error(__('Não foi possível salvar o item do carrinho. Tente novamente.'));
        }

        $plants = $plantsTable->find('list', ['limit' => 200]);
        $this->set(compact('cartItem', 'plants'));
    }

    public function delete($id = null)
    {
        $this->request->allowMethod(['post', 'delete']);

        $plantsTable = $this->getTableLocator()->get('Plants');

        $cartItem = $this->CartItems->get($id, ['contain' => ['Plants']]);
        $plant = $cartItem->plant;

        if ($this->CartItems->delete($cartItem)) {
            $plant->stock += $cartItem->quantity;
            $plantsTable->save($plant);

            $this->Flash->success(__('Item removido do carrinho.'));
        } else {
            $this->Flash->error(__('Não foi possível remover o item do carrinho.'));
        }

        return $this->redirect($this->referer());
    }
}

