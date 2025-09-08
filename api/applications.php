<?php
require_once 'config.php';

$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
    case 'POST':
        // Get posted data
        $data = json_decode(file_get_contents('php://input'), true);
        
        try {
            $stmt = $pdo->prepare("INSERT INTO applications (
                application_number,
                application_type,
                full_name,
                nik,
                birth_place,
                birth_date,
                gender,
                marital_status,
                address,
                phone,
                email,
                occupation,
                education,
                appointment_date,
                appointment_time,
                status,
                created_at,
                payment_status,
                document_ktp,
                document_kk,
                document_birth_cert,
                document_photo
            ) VALUES (
                :application_number,
                :application_type,
                :full_name,
                :nik,
                :birth_place,
                :birth_date,
                :gender,
                :marital_status,
                :address,
                :phone,
                :email,
                :occupation,
                :education,
                :appointment_date,
                :appointment_time,
                'pending',
                NOW(),
                'waiting',
                :document_ktp,
                :document_kk,
                :document_birth_cert,
                :document_photo
            )");

            $stmt->execute([
                ':application_number' => $data['applicationNumber'],
                ':application_type' => $data['applicationType'],
                ':full_name' => $data['fullName'],
                ':nik' => $data['nik'],
                ':birth_place' => $data['birthPlace'],
                ':birth_date' => $data['birthDate'],
                ':gender' => $data['gender'],
                ':marital_status' => $data['maritalStatus'],
                ':address' => $data['address'],
                ':phone' => $data['phone'],
                ':email' => $data['email'],
                ':occupation' => $data['occupation'],
                ':education' => $data['education'],
                ':appointment_date' => $data['appointmentDate'],
                ':appointment_time' => $data['appointmentTime'],
                ':document_ktp' => $data['documents']['ktp'],
                ':document_kk' => $data['documents']['kk'],
                ':document_birth_cert' => $data['documents']['birthCertificate'],
                ':document_photo' => $data['documents']['photo']
            ]);

            $applicationId = $pdo->lastInsertId();

            // Create notification for admin
            $stmt = $pdo->prepare("INSERT INTO admin_notifications (
                type,
                title,
                message,
                application_id,
                is_read,
                created_at
            ) VALUES (
                'new_application',
                'Permohonan Baru',
                :message,
                :application_id,
                0,
                NOW()
            )");

            $stmt->execute([
                ':message' => "{$data['fullName']} mengajukan permohonan paspor baru dengan nomor {$data['applicationNumber']}",
                ':application_id' => $applicationId
            ]);

            echo json_encode([
                'success' => true,
                'message' => 'Application submitted successfully',
                'applicationId' => $applicationId
            ]);
        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'message' => 'Error submitting application: ' . $e->getMessage()
            ]);
        }
        break;

    case 'PUT':
        $data = json_decode(file_get_contents('php://input'), true);
        $id = $_GET['id'] ?? null;

        if (!$id) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Application ID is required']);
            break;
        }

        try {
            $stmt = $pdo->prepare("UPDATE applications SET
                office_id = :office_id,
                office_name = :office_name,
                office_address = :office_address,
                passport_type = :passport_type,
                passport_price = :passport_price,
                updated_at = NOW()
                WHERE id = :id");

            $stmt->execute([
                ':office_id' => $data['officeId'],
                ':office_name' => $data['officeName'],
                ':office_address' => $data['officeAddress'],
                ':passport_type' => $data['passportType'],
                ':passport_price' => $data['passportPrice'],
                ':id' => $id
            ]);

            // Create admin notification
            $stmt = $pdo->prepare("INSERT INTO admin_notifications (
                type,
                title,
                message,
                application_id,
                is_read,
                created_at
            ) VALUES (
                'location_selected',
                'Lokasi & Jenis Paspor Dipilih',
                :message,
                :application_id,
                0,
                NOW()
            )");

            $stmt->execute([
                ':message' => "{$data['fullName']} telah memilih {$data['officeName']} dan paspor {$data['passportType']}",
                ':application_id' => $id
            ]);

            echo json_encode([
                'success' => true,
                'message' => 'Application updated successfully'
            ]);
        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'message' => 'Error updating application: ' . $e->getMessage()
            ]);
        }
        break;

    case 'GET':
        $id = $_GET['id'] ?? null;
        
        try {
            if ($id) {
                // Get single application
                $stmt = $pdo->prepare("SELECT * FROM applications WHERE id = ?");
                $stmt->execute([$id]);
                $result = $stmt->fetch();
            } else {
                // Get all applications
                $stmt = $pdo->query("SELECT * FROM applications ORDER BY created_at DESC");
                $result = $stmt->fetchAll();
            }

            echo json_encode([
                'success' => true,
                'data' => $result
            ]);
        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'message' => 'Error fetching applications: ' . $e->getMessage()
            ]);
        }
        break;

    default:
        http_response_code(405);
        echo json_encode(['success' => false, 'message' => 'Method not allowed']);
        break;
}
?>
