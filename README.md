# Online Course Website (Customizable E-Learning Platform) - Overview
This web project is an assignment for Capstone Project module in semester 5 of my diploma program at APU, which is also my final year project. My teammates include Kee Wen Yew, Joshua Liew Yi-Way, Ivan Chin Li Ming, Colwyn Pang, and Ann Wen Kai. The website created - Core of Courses, is a personalised e-learning platform that provides students with a wide variety of online IT courses. Personalisation is achievable by allowing student users to answer some questions (survey) related to their academic interests or preferences during sign-up. Therefore, our system can retrieve said information and utilise them to display relevant courses that closely match with the student's preferences. Our main intention in creating this platform is to have a place for undergraduate students to enhance their skills or learn new skills. Additionally, if student users find necessary, they can get in touch with lecturer users through our chat and forum functionalities to have a greater understanding of any particular course or IT domain. 

Our project objectives include:
- help students to improve on their studies or learn extra skills / knowledge on their own
- promote independant learning and provide sufficient assistance in the self-learning process
- enhance students' e-learning outcomes
- shape a platform that acts as a IT knowledge hub - users can enquire, discuss and share knowledge
- foster networking and collaboration by enabling meaningful and insightful interactions

Similar to the quiz publishing website that I have developed in my diploma semester 4, this is a locally-hosted project that implements XAMPP as a local server. The development has involved four programming / scripting languages - HTML, CSS, JavaScript and PHP. In order to retrieve data from XAMPP server, MySQL query language is used.


# Project Scope and Assumptions (How It Works)
### Scope
1.	Create a website that are compatible with desktop and mobile usage (responsive web design).
Note: unfortunately, due to time constraints, not all pages are fully-responsive.

3.	The website is accessible using a browser, without needing to download any application.
4.	Our e-learning platform specialises in providing educational courses related to IT fields such as computer science, data analytics, AI, etc. 
5.	The e-learning platform customises its behaviour by allowing student users to choose the careers, fields and learning styles that suit their liking during profile setup. 
6.	English will be the only available language and the standard for all course materials, and the same applies to the website’s language. 
7.	The targeted student users are undergraduates who aspire to improving their IT skills, while the targeted course creators are certified lecturers from established universities. 
8.	The targeted course creating users are tertiary education lecturers whose legitimacy and qualification are verified by website administrators.
9.	Website administrators (admin) have the authority to perform CRUD (create, read, update, delete) action across multiple aspects of the website intended for operations management.
10.	Each user type (role) has their respective user interface.

### Assumptions
1.	Users who are applying to become a course creator will need to submit a resume and relevant documents to be approved by admins in order to start publishing courses on the website. Said documents can include proof of academic qualification, teaching certification, portfolio or other documentation that can demonstrate the expertise of the user.
2.	Admin of the website is presumably knowledgeable in verifying course creator applicant’s resume and qualification proofs. This also includes being able to identify fraudulent documents. 
3.	Each user can only submit course creator application one time per day to prevent spam.
4.	The e-learning platform will be locally hosted, which means it is not accessible with a public domain name. 

### How to Use
#### 1. MySQL Database Import
Download the sql file, *cocdb_database.sql*. Then, import it directly into XAMPP (phpMyAdmin) database. The database import can be done after typing 'localhost/phpmyadmin' to visit phpMyAdmin page (alternatively, click 'Admin' in XAMPP control panel). Once successful, a database with the name "morningkdb" will be created and used. The connection would then be established by opening XAMPP control panel and activating (click 'Start') on two modules - Apache and MySQL.

#### 2. Project Folder Placement in htdocs
Assuming that XAMPP is successfully installed, all the program files and folders is downloaded and contained in a parent folder (any name). After that, this particular parent folder is moved (or copy, paste) to the "htdocs" folder, which is a child folder of xampp folder. The xampp folder should have been automatically created after XAMPP installation and usually located in C drive. This, and alongside step 1, ensure that the php files can connect to XAMPP's MySQL server.

Note: there is a *conn.php* file among the files that serves the purpose of establishing a server connection. Make sure the database name in the file matches with the name of the database imported in phpMyAdmin.

#### 3. Password Hashing
As a security implementation, all user passwords are hashed (encrypted) in the database. You can visit the user table in the database, and you will find that the values of the password columns are all giberrish. This is completely normal and intended. This means that even if the database is somehow intruded with brute force, the password are hidden, and impersonation attempts will not be successful. In relation to that, password can be reset by the user in his/her profile section.

This is a list of usernames and passwords that can be used to log into the system:

**Admin Accounts:** 
- user: ivan@gmail.com, password: Ivan123.
- user: adam@gmail.com, password: Adam123.

**Lecturer Login:** 
- user: salasiah@gmail.com, password: Salasiah123.
- user: stewart@gmail.com, password: Stewart123.

**Student Login:**
- user: lindalim@gmail.com, password: Lindalim123.
- user: bernard@gmail.com, password: Bernard123.
- user: muthu123@gmail.com, password: Muthu123.
- user: wenyew@gmail.com, password: Kee1234.

**Lecturer Applicant Login:**
- user: anwar@hotmail.com, password: Anwar123.
