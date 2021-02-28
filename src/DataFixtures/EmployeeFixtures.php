<?php

namespace App\DataFixtures;

use App\Entity\Employee;
use App\Entity\Category;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class EmployeeFixtures extends Fixture
{

    public function load(ObjectManager $manager): void
    {
        $employeeRepo = $manager->getRepository('App\Entity\Employee');
        $categoryRepo = $manager->getRepository('App\Entity\Category');

        foreach ($this->getEmployeeData() as [$firstName, $lastName, $email, $categoryName, $parentEmail]) {

            $category = $categoryRepo->findOneByCategory($categoryName);
            if( $category === null ) {
                $category = new Category();
                $category->setName($categoryName);
                $manager->persist($category);
            }

            $employee = new Employee();
            $employee->setFirstname($firstName);
            $employee->setLastname($lastName);
            $employee->setEmail($email);
            $employee->setCategory($category);
            $employee->setParentEmail($parentEmail);

            $employee->setParent($employeeRepo->findOneByEmail($employee->getParentEmail()));

            $manager->persist($employee);
            $manager->flush();
        }

    }

    private function getEmployeeData(): array
    {
        return [
            //$employeeData = [$firstName, $lastName, $email, $categoryName, $parentEmail];
            ['CEO', 'X', 'ceo@example.com', 'Board', '-',],
            ['Head', 'A', 'head.a@example.com', 'Management', 'ceo@example.com'],
            ['Head', 'B', 'head.b@example.com', 'Management', 'ceo@example.com'],
            ['Manager', 'C', 'manager.c@example.com', 'Management', 'head.a@example.com'],
            ['Manager', 'D', 'manager.d@example.com', 'Management', 'head.a@example.com'],
            ['Accounter', 'H', 'accounter.h@example.com', 'Accounting', 'head.b@example.com'],
            ['Developer', 'E', 'developer.e@example.com', 'Development', 'manager.c@example.com'],
            ['Developer', 'G', 'developer.g@example.com', 'Development', 'manager.d@example.com'],
            ['Designer', 'F', 'designer.f@example.com', 'Development', 'manager.c@example.com'],
        ];
    }
}
