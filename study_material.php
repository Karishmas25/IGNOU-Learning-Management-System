<?php

session_start();

require_once "db_connect.php";

require_once "semester_config.php";


/*
 * Study Materials
 *
 * Purpose:
 * Displays study materials according to the selected
 * programme, semester and category.
 */


/*
 * Check whether the student is logged in.
 */
if (!isset($_SESSION["userId"])) {

    header("Location: login.php");

    exit();

}


/*
 * Get selected programme, semester,
 * category and search text.
 */
$programme = $_GET["programme"] ?? "";

$semester = isset($_GET["semester"])
    ? (int) $_GET["semester"]
    : 0;

$category = $_GET["category"] ?? "";

$search = trim(
    $_GET["search"] ?? ""
);


/*
 * Define the supported programmes.
 *
 * The current project demonstrates BCA.
 *
 * Additional programmes can be added
 * here in the future.
 */
$validProgrammes = [
    "BCA" => "Bachelor of Computer Applications"
];


/*
 * Define valid study material categories.
 */
$validCategories = [
    "notes",
    "resources",
    "papers",
    "solved"
];


/*
 * Validate programme.
 */
if (
    $programme !== "" &&
    !array_key_exists(
        $programme,
        $validProgrammes
    )
) {

    $programme = "";

    $semester = 0;

    $category = "";

    $search = "";

}


/*
 * Validate semester.
 *
 * Only semesters 1 to 6 are displayed.
 */
if (
    $semester < 0 ||
    $semester > 6
) {

    $semester = 0;

    $category = "";

    $search = "";

}


/*
 * Category cannot be selected before
 * a valid programme and semester are selected.
 */
if (
    $category !== "" &&
    (
        $programme === "" ||
        $semester === 0 ||
        !in_array(
            $category,
            $validCategories,
            true
        )
    )
) {

    $category = "";

    $search = "";

}


/*
 * Set category heading.
 */
$heading = "Study Materials";


if ($category === "notes") {

    $heading = "Notes";

} elseif ($category === "resources") {

    $heading = "Learning Resources";

} elseif ($category === "papers") {

    $heading = "Previous Year Papers";

} elseif ($category === "solved") {

    $heading = "Solved Papers";

}


/*
 * Get selected programme name.
 */
$programmeName = "";

if (
    $programme !== "" &&
    isset($validProgrammes[$programme])
) {

    $programmeName =
        $validProgrammes[$programme];

}


/*
 * Get subjects belonging to
 * the selected semester.
 */
$semesterSubjectsList = [];


if (
    $semester > 0 &&
    isset($semesterSubjects[$semester])
) {

    $semesterSubjectsList =
        $semesterSubjects[$semester];

}


/*
 * Result variable.
 */
$result = null;

$stmt = null;


/*
 * Fetch materials only when a category
 * has been selected.
 */
if (
    $programme === "BCA" &&
    $semester > 0 &&
    $category !== ""
) {


    /*
     * If the selected semester does not
     * currently have materials, no
     * database query is required.
     */
    if (count($semesterSubjectsList) > 0) {


        /*
         * Create placeholders according
         * to the number of subjects.
         */
        $subjectPlaceholders = implode(
            ", ",
            array_fill(
                0,
                count($semesterSubjectsList),
                "?"
            )
        );


        /*
         * Prepare category title values.
         */
        if ($category === "notes") {

            $categoryValue1 = "%Note%";

            $categoryValue2 = "%Notes%";

        } elseif ($category === "resources") {

            $categoryValue1 = "%Resource%";

            $categoryValue2 = "%Resources%";

        } elseif ($category === "papers") {

            $categoryValue1 = "%Previous Year%";

            $categoryValue2 = "%Question Paper%";

        } else {

            $categoryValue1 = "%Solved Paper%";

            $categoryValue2 = "%Solved%";

        }


        /*
         * Build the SQL query.
         *
         * The subject codes are taken from
         * semester_config.php.
         */
        $sql = "SELECT
                    materialId,
                    title,
                    subjectCode,
                    filePath
                FROM studymaterial
                WHERE
                    (
                        title LIKE ?
                        OR title LIKE ?
                    )
                AND subjectCode IN (
                    $subjectPlaceholders
                )";


        /*
         * Add search condition when
         * the student searches.
         */
        if ($search !== "") {

            $sql .= "
                AND (
                    title LIKE ?
                    OR subjectCode LIKE ?
                )
            ";

        }


        /*
         * Order newest materials first.
         */
        $sql .= "
            ORDER BY materialId DESC
        ";


        /*
         * Prepare the query.
         */
        $stmt = $conn->prepare($sql);


        /*
         * Check whether the query
         * was prepared successfully.
         */
        if (!$stmt) {

            die(
                "Unable to process study materials."
            );

        }


        /*
         * Prepare parameter values.
         */
        $types = "ss";

        $parameters = [
            $categoryValue1,
            $categoryValue2
        ];


        /*
         * Add semester subject codes.
         */
        foreach (
            $semesterSubjectsList
            as $subjectCode
        ) {

            $types .= "s";

            $parameters[] =
                $subjectCode;

        }


        /*
         * Add search parameters when required.
         */
        if ($search !== "") {

            $searchValue =
                "%" . $search . "%";

            $types .= "ss";

            $parameters[] =
                $searchValue;

            $parameters[] =
                $searchValue;

        }


        /*
         * Bind all parameters.
         */
        $stmt->bind_param(
            $types,
            ...$parameters
        );


        /*
         * Execute the query.
         */
        if (!$stmt->execute()) {

            $stmt->close();

            die(
                "Unable to load study materials."
            );

        }


        /*
         * Get the query result.
         */
        $result =
            $stmt->get_result();


        /*
         * Check whether result was obtained.
         */
        if ($result === false) {

            $stmt->close();

            die(
                "Unable to retrieve study materials."
            );

        }

    }

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
        Study Materials - IGNOU Learning Management System
    </title>


    <!-- Common stylesheet -->

    <link
        rel="stylesheet"
        href="style.css?v=8"
    >

</head>


<body class="study-material-page">


<!-- Header section -->

<div class="study-material-header">

    <div class="study-material-header-inner">

        <h2>
            Learning Management System for IGNOU Students
        </h2>

    </div>

</div>


<div class="study-material-container">


<?php if ($programme === ""): ?>


    <!--
        PROGRAMME SELECTION
    -->

    <h1>
        Study Materials
    </h1>


    <div class="study-material-selection">


        <!-- Programme selection -->

        <div class="study-material-selection-card">

            <h3>
                Select Programme
            </h3>

            <p>
                Select your academic programme
                to view the available study materials.
            </p>


            <form
                method="GET"
                action="study_material.php"
            >


                <select
                    name="programme"
                    required
                >

                    <option
                        value=""
                        selected
                        disabled
                    >
                        Select Programme
                    </option>


                    <?php foreach (
                        $validProgrammes
                        as $programmeCode =>
                        $programmeTitle
                    ): ?>

                        <option
                            value="<?php
                            echo htmlspecialchars(
                                $programmeCode,
                                ENT_QUOTES,
                                "UTF-8"
                            );
                            ?>"
                        >

                            <?php
                            echo htmlspecialchars(
                                $programmeTitle,
                                ENT_QUOTES,
                                "UTF-8"
                            );
                            ?>

                            (
                            <?php
                            echo htmlspecialchars(
                                $programmeCode,
                                ENT_QUOTES,
                                "UTF-8"
                            );
                            ?>
                            )

                        </option>

                    <?php endforeach; ?>


                </select>


                <button
                    type="submit"
                >
                    Continue
                </button>


            </form>

        </div>


    </div>


<?php elseif ($semester === 0): ?>


    <!--
        SEMESTER SELECTION
    -->

    <h1>

        <?php
        echo htmlspecialchars(
            $programme,
            ENT_QUOTES,
            "UTF-8"
        );
        ?>

        - Select Semester

    </h1>


    <div class="categories">


        <!-- Semester 1 -->

        <div class="category">

            <h3>
                Semester 1
            </h3>

            <p>
                Study materials for Semester 1.
            </p>

            <a
                href="study_material.php?programme=<?php
                echo urlencode($programme);
                ?>&semester=1"
            >
                Select Semester
            </a>

        </div>


        <!-- Semester 2 -->

        <div class="category">

            <h3>
                Semester 2
            </h3>

            <p>
                Study materials for Semester 2.
            </p>

            <a
                href="study_material.php?programme=<?php
                echo urlencode($programme);
                ?>&semester=2"
            >
                Select Semester
            </a>

        </div>


        <!-- Semester 3 -->

        <div class="category">

            <h3>
                Semester 3
            </h3>

            <p>
                Study materials for Semester 3.
            </p>

            <a
                href="study_material.php?programme=<?php
                echo urlencode($programme);
                ?>&semester=3"
            >
                Select Semester
            </a>

        </div>


        <!-- Semester 4 -->

        <div class="category">

            <h3>
                Semester 4
            </h3>

            <p>
                Study materials for Semester 4.
            </p>

            <a
                href="study_material.php?programme=<?php
                echo urlencode($programme);
                ?>&semester=4"
            >
                Select Semester
            </a>

        </div>


        <!-- Semester 5 -->

        <div class="category">

            <h3>
                Semester 5
            </h3>

            <p>
                Study materials for Semester 5.
            </p>

            <a
                href="study_material.php?programme=<?php
                echo urlencode($programme);
                ?>&semester=5"
            >
                Select Semester
            </a>

        </div>


        <!-- Semester 6 -->

        <div class="category">

            <h3>
                Semester 6
            </h3>

            <p>
                Study materials for Semester 6.
            </p>

            <a
                href="study_material.php?programme=<?php
                echo urlencode($programme);
                ?>&semester=6"
            >
                Select Semester
            </a>

        </div>


    </div>


    <br>


    <!-- Back to programme selection -->

    <a
        class="back"
        href="study_material.php"
    >
        ← Back to Programmes
    </a>


<?php elseif ($category === ""): ?>


    <!--
        CATEGORY SELECTION
    -->

    <h1>

        <?php
        echo htmlspecialchars(
            $programme,
            ENT_QUOTES,
            "UTF-8"
        );
        ?>

        -

        Semester

        <?php
        echo htmlspecialchars(
            $semester,
            ENT_QUOTES,
            "UTF-8"
        );
        ?>

    </h1>


    <div class="categories">


        <!-- Notes -->

        <div class="category">

            <h3>
                Notes
            </h3>

            <p>
                Subject-wise study notes.
            </p>

            <a
                href="study_material.php?programme=<?php
                echo urlencode($programme);
                ?>&semester=<?php
                echo $semester;
                ?>&category=notes"
            >
                View Notes
            </a>

        </div>


        <!-- Learning Resources -->

        <div class="category">

            <h3>
                Learning Resources
            </h3>

            <p>
                Additional learning resources.
            </p>

            <a
                href="study_material.php?programme=<?php
                echo urlencode($programme);
                ?>&semester=<?php
                echo $semester;
                ?>&category=resources"
            >
                View Resources
            </a>

        </div>


        <!-- Previous Year Papers -->

        <div class="category">

            <h3>
                Previous Year Papers
            </h3>

            <p>
                Previous year examination papers.
            </p>

            <a
                href="study_material.php?programme=<?php
                echo urlencode($programme);
                ?>&semester=<?php
                echo $semester;
                ?>&category=papers"
            >
                View Papers
            </a>

        </div>


        <!-- Solved Papers -->

        <div class="category">

            <h3>
                Solved Papers
            </h3>

            <p>
                Previous year solved papers.
            </p>

            <a
                href="study_material.php?programme=<?php
                echo urlencode($programme);
                ?>&semester=<?php
                echo $semester;
                ?>&category=solved"
            >
                View Solved Papers
            </a>

        </div>


    </div>


    <br>


    <!-- Back to semester selection -->

    <a
        class="back"
        href="study_material.php?programme=<?php
        echo urlencode($programme);
        ?>"
    >
        ← Back to Semesters
    </a>


<?php else: ?>


    <!--
        SELECTED CATEGORY
    -->

    <h1>

        <?php
        echo htmlspecialchars(
            $heading,
            ENT_QUOTES,
            "UTF-8"
        );
        ?>

    </h1>


    <p>

        <strong>
            Programme:
        </strong>

        <?php
        echo htmlspecialchars(
            $programmeName,
            ENT_QUOTES,
            "UTF-8"
        );
        ?>

        (
        <?php
        echo htmlspecialchars(
            $programme,
            ENT_QUOTES,
            "UTF-8"
        );
        ?>
        )

        &nbsp;&nbsp;

        <strong>
            Semester:
        </strong>

        <?php
        echo htmlspecialchars(
            $semester,
            ENT_QUOTES,
            "UTF-8"
        );
        ?>

    </p>


    <!-- Search study materials -->

    <div class="search-box">

        <form method="GET">


            <input
                type="hidden"
                name="programme"
                value="<?php
                echo htmlspecialchars(
                    $programme,
                    ENT_QUOTES,
                    "UTF-8"
                );
                ?>"
            >


            <input
                type="hidden"
                name="semester"
                value="<?php
                echo htmlspecialchars(
                    $semester,
                    ENT_QUOTES,
                    "UTF-8"
                );
                ?>"
            >


            <input
                type="hidden"
                name="category"
                value="<?php
                echo htmlspecialchars(
                    $category,
                    ENT_QUOTES,
                    "UTF-8"
                );
                ?>"
            >


            <input
                type="text"
                name="search"
                placeholder="Search by title or subject code"
                value="<?php
                echo htmlspecialchars(
                    $search,
                    ENT_QUOTES,
                    "UTF-8"
                );
                ?>"
            >


            <button type="submit">
                Search
            </button>


        </form>

    </div>


    <!-- Study material table -->

    <table>

        <tr>

            <th>
                Material ID
            </th>

            <th>
                Title
            </th>

            <th>
                Subject Code
            </th>

            <th>
                Resource
            </th>

        </tr>


        <?php if (
            $result !== null &&
            $result->num_rows > 0
        ): ?>


            <?php while (
                $row =
                $result->fetch_assoc()
            ): ?>


                <tr>

                    <td>

                        <?php
                        echo htmlspecialchars(
                            $row["materialId"],
                            ENT_QUOTES,
                            "UTF-8"
                        );
                        ?>

                    </td>


                    <td>

                        <?php
                        echo htmlspecialchars(
                            $row["title"],
                            ENT_QUOTES,
                            "UTF-8"
                        );
                        ?>

                    </td>


                    <td>

                        <?php
                        echo htmlspecialchars(
                            $row["subjectCode"],
                            ENT_QUOTES,
                            "UTF-8"
                        );
                        ?>

                    </td>


                    <td>

                        <a
                            class="resource-link"
                            href="<?php
                            echo htmlspecialchars(
                                $row["filePath"],
                                ENT_QUOTES,
                                "UTF-8"
                            ); ?>"
                            target="_blank"
                            rel="noopener noreferrer"
                        >
                            View / Download
                        </a>

                    </td>

                </tr>


            <?php endwhile; ?>


        <?php else: ?>


            <tr>

                <td colspan="4">

                    No materials found for

                    <?php
                    echo htmlspecialchars(
                        $programme,
                        ENT_QUOTES,
                        "UTF-8"
                    );
                    ?>

                    Semester

                    <?php
                    echo htmlspecialchars(
                        $semester,
                        ENT_QUOTES,
                        "UTF-8"
                    );
                    ?>.

                </td>

            </tr>


        <?php endif; ?>


    </table>


    <br>


    <!-- Back to category selection -->

    <a
        class="back"
        href="study_material.php?programme=<?php
        echo urlencode($programme);
        ?>&semester=<?php
        echo $semester;
        ?>"
    >
        ← Back to Categories
    </a>


<?php endif; ?>


<br>


<!-- Back to student dashboard -->

<a
    class="back"
    href="student_dashboard.php"
>
    ← Back to Dashboard
</a>


</div>


<?php

/*
 * Close the prepared statement
 * when it was created.
 */
if ($stmt !== null) {

    $stmt->close();

}

?>


</body>

</html>