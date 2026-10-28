<?php

session_start();

require_once "db_connect.php";


/*
 * Admin Study Material Management
 *
 * Allows an admin to:
 * - Add study materials
 * - Update study materials
 * - Delete study materials
 */


// Check whether the user is logged in
if (!isset($_SESSION["userId"])) {

    header("Location: login.php");
    exit();
}


// Check whether the logged-in user is an admin
if (!isset($_SESSION["role"]) || $_SESSION["role"] !== "Admin") {

    header("Location: student_dashboard.php");
    exit();
}


$message = "";


// Variables used for update/edit mode
$editMode = false;

$editMaterialId = 0;

$editTitle = "";

$editSubjectCode = "";

$editFilePath = "";


// LOAD MATERIAL FOR EDIT

if (isset($_GET["edit"])) {

    $materialId = (int) $_GET["edit"];


    if ($materialId > 0) {

        $stmt = $conn->prepare(
            "SELECT materialId, title, subjectCode, filePath
             FROM studymaterial
             WHERE materialId = ?"
        );


        if ($stmt) {

            $stmt->bind_param(
                "i",
                $materialId
            );


            $stmt->execute();


            $editResult = $stmt->get_result();


            if ($editResult->num_rows === 1) {

                $editRow = $editResult->fetch_assoc();


                $editMode = true;

                $editMaterialId = $editRow["materialId"];

                $editTitle = $editRow["title"];

                $editSubjectCode = $editRow["subjectCode"];

                $editFilePath = $editRow["filePath"];

            }


            $stmt->close();

        }

    }

}


// HANDLE FORM SUBMISSION

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $action = $_POST["action"] ?? "";


    // ADD STUDY MATERIAL

    if ($action === "add_material") {

        $title = trim(
            $_POST["title"] ?? ""
        );

        $subjectCode = trim(
            $_POST["subjectCode"] ?? ""
        );

        $filePath = trim(
            $_POST["filePath"] ?? ""
        );


        // Check whether all fields are filled
        if (
            $title === "" ||
            $subjectCode === "" ||
            $filePath === ""
        ) {

            $message = "Please fill all fields.";

        } else {

            $stmt = $conn->prepare(
                "INSERT INTO studymaterial
                (title, subjectCode, filePath)
                VALUES (?, ?, ?)"
            );


            if ($stmt) {

                $stmt->bind_param(
                    "sss",
                    $title,
                    $subjectCode,
                    $filePath
                );


                if ($stmt->execute()) {

                    $message =
                        "Study material added successfully.";

                } else {

                    $message =
                        "Unable to add study material.";
                }


                $stmt->close();

            } else {

                $message =
                    "Unable to process the request.";
            }

        }

    }


    // UPDATE STUDY MATERIAL

    elseif ($action === "update_material") {

        $materialId = (int) (
            $_POST["materialId"] ?? 0
        );

        $title = trim(
            $_POST["title"] ?? ""
        );

        $subjectCode = trim(
            $_POST["subjectCode"] ?? ""
        );

        $filePath = trim(
            $_POST["filePath"] ?? ""
        );


        // Validate update details
        if (
            $materialId <= 0 ||
            $title === "" ||
            $subjectCode === "" ||
            $filePath === ""
        ) {

            $message =
                "Please fill all fields.";

        } else {

            $stmt = $conn->prepare(
                "UPDATE studymaterial
                 SET title = ?,
                     subjectCode = ?,
                     filePath = ?
                 WHERE materialId = ?"
            );


            if ($stmt) {

                $stmt->bind_param(
                    "sssi",
                    $title,
                    $subjectCode,
                    $filePath,
                    $materialId
                );


                if ($stmt->execute()) {

                    $message =
                        "Study material updated successfully.";

                } else {

                    $message =
                        "Unable to update study material.";
                }


                $stmt->close();

            } else {

                $message =
                    "Unable to process the update request.";
            }

        }

    }

}


// DELETE STUDY MATERIAL

if (isset($_GET["delete"])) {

    $materialId = (int) $_GET["delete"];


    if ($materialId > 0) {

        $stmt = $conn->prepare(
            "DELETE FROM studymaterial
             WHERE materialId = ?"
        );


        if ($stmt) {

            $stmt->bind_param(
                "i",
                $materialId
            );


            if ($stmt->execute()) {

                $stmt->close();


                // Refresh the page after deletion
                header(
                    "Location: admin_materials.php"
                );

                exit();

            } else {

                $message =
                    "Unable to delete study material.";

                $stmt->close();
            }

        } else {

            $message =
                "Unable to process the delete request.";
        }

    }

}


// GET ALL STUDY MATERIALS

$result = $conn->query(
    "SELECT materialId, title, subjectCode, filePath
     FROM studymaterial
     ORDER BY materialId DESC"
);


if (!$result) {

    die(
        "Unable to retrieve study materials."
    );

}

?>

<!DOCTYPE html>

<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>
        Study Materials
    </title>

    <link
        rel="stylesheet"
        href="style.css?v=3"
    >

</head>


<body class="admin-materials-page">


<!--
    ADMIN MATERIALS HEADER
-->

<div class="admin-materials-header">

    <h2>
        Learning Management System for IGNOU Students
    </h2>

</div>


<!-- Main content container -->

<div class="admin-materials-container">


    <!--
        PAGE TITLE AND BACK LINK
    -->

    <div class="admin-materials-title-row">

        <h1 class="admin-materials-title">
            Study Materials
        </h1>


        <a
            href="admin_dashboard.php"
            class="admin-materials-back"
        >
            Back to Dashboard
        </a>

    </div>


    <!--
        DISPLAY MESSAGE
    -->

    <?php if ($message !== ""): ?>

        <div class="admin-materials-message">

            <?php

            echo htmlspecialchars(
                $message
            );

            ?>

        </div>

    <?php endif; ?>


    <!--
        ADD / UPDATE STUDY MATERIAL
    -->

    <div class="admin-materials-card">

        <h3>

            <?php if ($editMode): ?>

                Update Study Material

            <?php else: ?>

                Add Study Material

            <?php endif; ?>

        </h3>


        <form
            method="POST"
            class="admin-materials-form"
        >


            <?php if ($editMode): ?>

                <!--
                    Hidden action used for update
                -->

                <input
                    type="hidden"
                    name="action"
                    value="update_material"
                >


                <!--
                    Material ID
                -->

                <input
                    type="hidden"
                    name="materialId"
                    value="<?php
                    echo (int) $editMaterialId;
                    ?>"
                >

            <?php else: ?>

                <!--
                    Hidden action used for adding
                -->

                <input
                    type="hidden"
                    name="action"
                    value="add_material"
                >

            <?php endif; ?>


            <!-- TITLE -->

            <label>
                Title:
            </label>

            <input
                type="text"
                name="title"
                value="<?php
                echo htmlspecialchars(
                    $editTitle
                );
                ?>"
                required
            >


            <!-- SUBJECT CODE -->

            <label>
                Subject Code:
            </label>

            <input
                type="text"
                name="subjectCode"
                value="<?php
                echo htmlspecialchars(
                    $editSubjectCode
                );
                ?>"
                placeholder="Example: BCS-052"
                required
            >


            <!-- FILE PATH -->

            <label>
                File Path:
            </label>

            <input
                type="text"
                name="filePath"
                value="<?php
                echo htmlspecialchars(
                    $editFilePath
                );
                ?>"
                placeholder="Example: materials/bcs052.pdf"
                required
            >


            <!-- BUTTONS -->

            <?php if ($editMode): ?>

                <div class="admin-materials-update-actions">

                    <button
                        type="submit"
                        class="admin-materials-update-submit"
                    >
                        Update Material
                    </button>


                    <a
                        href="admin_materials.php"
                        class="admin-materials-cancel"
                    >
                        Cancel
                    </a>

                </div>

            <?php else: ?>

                <button
                    type="submit"
                    class="admin-materials-submit"
                >
                    Add Material
                </button>

            <?php endif; ?>


        </form>

    </div>


    <!--
        AVAILABLE STUDY MATERIALS
    -->

    <div
        class="
            admin-materials-card
            admin-materials-list-card
        "
    >

        <h3>
            Available Study Materials
        </h3>


        <div
            class="admin-materials-table-wrapper"
        >

            <table
                class="admin-materials-table"
            >

                <tr>

                    <th>
                        Title
                    </th>

                    <th>
                        Subject
                    </th>

                    <th>
                        File
                    </th>

                    <th>
                        Action
                    </th>

                </tr>


                <?php while (
                    $row = $result->fetch_assoc()
                ): ?>

                    <tr>

                        <!-- TITLE -->

                        <td>

                            <?php

                            echo htmlspecialchars(
                                $row["title"]
                            );

                            ?>

                        </td>


                        <!-- SUBJECT -->

                        <td>

                            <?php

                            echo htmlspecialchars(
                                $row["subjectCode"]
                            );

                            ?>

                        </td>


                        <!-- FILE -->

                        <td>

                            <a
                                href="<?php

                                echo htmlspecialchars(
                                    $row["filePath"]
                                );

                                ?>"
                                target="_blank"
                                class="admin-materials-open"
                            >
                                Open
                            </a>

                        </td>


                        <!-- ACTION -->

                        <td class="admin-materials-action">

                            <!-- EDIT -->

                            <a
                                href="admin_materials.php?edit=<?php

                                echo (int)
                                    $row["materialId"];

                                ?>"
                                class="admin-materials-update"
                            >
                                Edit
                            </a>


                            <!-- DELETE -->

                            <a
                                href="admin_materials.php?delete=<?php

                                echo (int)
                                    $row["materialId"];

                                ?>"
                                class="admin-materials-delete"
                                onclick="
                                    return confirm(
                                        'Delete this material?'
                                    );
                                "
                            >
                                Delete
                            </a>

                        </td>

                    </tr>

                <?php endwhile; ?>


            </table>

        </div>

    </div>


</div>


</body>

</html>               