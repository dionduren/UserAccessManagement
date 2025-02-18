<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class EmployeeController extends Controller
{
    protected function getKaryawanDetail($nik, &$errors)
    {
        $sso_username = 'AP130';
        $sso_password = '1ASjhbjAs87ddsd9ASdbhjbdPOWdbh2m';

        $ssoApiUrl = 'https://sso.pupuk-indonesia.com/api/login/masterkary?badge=' . $nik;

        try {
            $response = Http::withBasicAuth($sso_username, $sso_password)->get($ssoApiUrl);

            // Check if the user is registered in SAP
            if ($response->successful() && $response['status']) {
                $body = $response->getBody();
                $detail_karyawan = json_decode($body, true);

                return $detail_karyawan;
            } else {
                $errors[] = "Employee number $nik not found.";
                return ['emp_no' => $nik, 'error' => true];
            }
        } catch (\Exception $e) {
            // Handle exceptions (like network issues)
            $errors[] = "Failed to fetch data for employee number $nik: " . $e->getMessage();
            return ['emp_no' => $nik, 'error' => true];
        }
    }

    public function fetchEmployeeData(Request $request)
    {
        $employeeNumbers = explode(',', $request->input('employee_numbers', ''));
        $employees = [];
        $errors = [];

        foreach ($employeeNumbers as $employeeNumber) {
            $employeeNumber = trim($employeeNumber);
            if (!empty($employeeNumber)) {
                $employeeData = $this->getKaryawanDetail($employeeNumber, $errors);
                $employees[] = $employeeData;
            }
        }

        $employeeNumber = $request();

        return view('employees', ['employees' => $employees, 'errors' => $errors]);
    }
}
