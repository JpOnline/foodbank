<?php

	$connection=mysqli_connect("dragon.ukc.ac.uk","cdms2","fa#roub", "cdms2");

	if (mysqli_connect_errno($connection)){
  		echo "Failed to connect to MySQL: " . mysqli_connect_error();
	}

	mysqli_query($connection, "insert into Client values (null, 'Ms', 'Jane', 'Smith', 1962-10-06, 'female', 'Darwin Houses WS06', 'UKCB', 'CT27NY', 'Canterbury', 'whitebritish')");			
	mysqli_query($connection, "insert into Client values (null, 'Mr', 'Raymond', 'Scarlett', 1970-09-16, 'male', 'Clowes Court 5D', 'UKC', 'CT27ST', 'Canterbury', 'whiteother')");
	mysqli_query($connection, "insert into Client values (null, 'Mrs', 'Annamarie', 'Deberry', 1982-10-26, 'female', 'Willows Court 2A', 'UKCB', 'CT27SW', 'Canterbury', 'mixedrace')");
	mysqli_query($connection, "insert into Client values (null, 'Ms', 'Zaida', 'Bove', 1969-03-12, 'female', 'Grimshill Court 1E', 'UKC', 'CT27SX', 'Canterbury', 'blackbritish')");
	mysqli_query($connection, "insert into Client values (null, 'Mr', 'Elliott', 'Hirai', 1963-02-11, 'male', 'Tudor Court 10E', 'UKCB', 'CT27SU', 'Canterbury', 'asianbritish')");
	mysqli_query($connection, "insert into Client values (null, 'Mrs', 'Daina', 'Woodward', 1961-01-01, 'female', 'Darwin Houses WAL05', 'UKCB', 'CT27NY', 'Canterbury', 'whitebritish')");
	
	mysqli_query($connection, "insert into Agency values (null, 'Catholic Church Whistable','1234','08767876345','07563452653','London Road 450','Village N','CT4YUN','Whistable')");
	mysqli_query($connection, "insert into Agency values (null, 'Catholic Church Canterbury','4321','07867564372','07587345678','North Homes Road','',' CT1 1QU','Canterbury')");
	mysqli_query($connection, "insert into Agency values (null, 'Canterbury Cathedral','7890','07867564321','0754321876','Cathedral House 11','The Precincts','CT1 2EH','Canterbury')");

	mysqli_query($connection, "insert into NatureOfNeed values ('asylum', 1)");
	mysqli_query($connection, "insert into NatureOfNeed values ('benefitschanged', 1)");
	mysqli_query($connection, "insert into NatureOfNeed values ('benefitsstopped', 2)");
	mysqli_query($connection, "insert into NatureOfNeed values ('childholidaymeals', 2)");
	mysqli_query($connection, "insert into NatureOfNeed values ('crisisloanrefused', 2)");
	mysqli_query($connection, "insert into NatureOfNeed values ('debt', 3)");
	mysqli_query($connection, "insert into NatureOfNeed values ('familycrisis', 3)");
	mysqli_query($connection, "insert into NatureOfNeed values ('sickness', 4)");
	mysqli_query($connection, "insert into NatureOfNeed values ('sofasurfing', 4)");
	mysqli_query($connection, "insert into NatureOfNeed values ('streethomeless', 5)");
	mysqli_query($connection, "insert into NatureOfNeed values ('unemployed', 5)");
	mysqli_query($connection, "insert into NatureOfNeed values ('waitingforbenefittostart', 6)");
	mysqli_query($connection, "insert into NatureOfNeed values ('zother, asdasdasd', 7)");	

	mysqli_query($connection, "insert into Voucher values (null,2,3,1,'1234','this agency is great.','2013-07-31',1,1)");		
	mysqli_query($connection, "insert into Voucher values (null,1,3,1,'7890','this agency is great.','2013-07-21',2,5)");	
	mysqli_query($connection, "insert into Voucher values (null,2,1,1,'1234','this agency is great.','2013-07-11',2,3)");	
	mysqli_query($connection, "insert into Voucher values (null,2,0,1,'4321','this agency is great.','2013-07-21',2,1)");	
	mysqli_query($connection, "insert into Voucher values (null,1,1,0,'1234','this agency is great.','2013-07-25',1,4)");	
	mysqli_query($connection, "insert into Voucher values (null,1,0,0,'4321','this agency is great.','2013-08-01',1,5)");	
	mysqli_query($connection, "insert into Voucher values (null,3,3,0,'1234','this agency is great.','2013-08-03',3,6)");	
	
	mysqli_query($connection, "insert into Warehouse values (null, 'Warehouse Canterbury 1', 'High Street 134', 'City Centre', 'CT6YH8', 'Canterbury', '04567876298', '09876756463')");
	mysqli_query($connection, "insert into Warehouse values (null, 'Warehouse Canterbury 2', 'High Street 431', 'City Centre', 'CT6YG8', 'Canterbury', '04523877698', '09236752363')");
	mysqli_query($connection, "insert into Warehouse values (null, 'Warehouse Whistable', 'High Street 14', 'City Centre', 'CT6ZH7', 'Whitstable', '04567876298', '09876756463')");
	mysqli_query($connection, "insert into Warehouse values (null, 'Warehouse Herne Bay', 'High Street 10', 'City Centre', 'CT6YH5', 'Herne Bay', '04567876298', '09876756463')");
	
	mysqli_query($connection, "insert into DistributionPoint values (null, 'DP Canterbury 1', 'High Street 194', 'City Centre', 'CT6YH8', 'Canterbury', '04567876298', '09876756463')");
	mysqli_query($connection, "insert into DistributionPoint values (null, 'DP Whistable 1', 'High Street 111', 'City Centre', 'CT6ZH7', 'Whistable', '01234876298', '09876755487')");
	mysqli_query($connection, "insert into DistributionPoint values (null, 'DP Herne Bay 1', 'High Street 222', 'City Centre', 'CN6YH5', 'Herne Bay', '04523456298', '09346756423')");
	
	mysqli_query($connection, "insert into FoodItem values (null, 'rice')");
    mysqli_query($connection, "insert into FoodItem values (null, 'baked beans')");	
	mysqli_query($connection, "insert into FoodItem values (null, 'jam')");
	mysqli_query($connection, "insert into FoodItem values (null, 'meat')");
	mysqli_query($connection, "insert into FoodItem values (null, 'sugar')");
	mysqli_query($connection, "insert into FoodItem values (null, 'milk')");
	mysqli_query($connection, "insert into FoodItem values (null, 'cereal')");
	
	mysqli_query($connection, "insert into Donation values (null, 'Supermarket', '2013-07-22', 1, 45)");
	mysqli_query($connection, "insert into Donation values (null, 'Normal', '2013-06-19', 2, 15)");
	mysqli_query($connection, "insert into Donation values (null, 'Tesco', '2013-05-21', 3, 20)");
	mysqli_query($connection, "insert into Donation values (null, 'Normal', '2013-08-02', 4, 50)");
	mysqli_query($connection, "insert into Donation values (null, 'ASDA', '2013-04-12', 1, 100)");
	
	mysqli_query($connection, "insert into Store values (50, 1, 1)");
	mysqli_query($connection, "insert into Store values (10, 2, 1)");
	mysqli_query($connection, "insert into Store values (20, 3, 1)");
	mysqli_query($connection, "insert into Store values (45, 4, 2)");
	mysqli_query($connection, "insert into Store values (80, 1, 3)");
	mysqli_query($connection, "insert into Store values (50, 6, 4)");
	mysqli_query($connection, "insert into Store values (50, 5, 2)");
	
	mysqli_query($connection, "insert into FoodParcel values (null, '2014-01-01', 'A0001', '2013-07-01', '1', 1, 0, 0, 1, 1)");	
	mysqli_query($connection, "insert into FoodParcel values (null, '2013-11-01', 'A0002', '2013-06-01', '1', 0, 1, 0, 1, 2)");	
	mysqli_query($connection, "insert into FoodParcel values (null, '2014-01-01', 'B0001', '2013-06-11', '1', 0, 0, 1, 2, 1)");	
	mysqli_query($connection, "insert into FoodParcel values (null, '2015-01-01', 'B0002', '2013-08-10', '1', 1, 0, 0, 2, 3)");	
	mysqli_query($connection, "insert into FoodParcel values (null, '2014-07-01', 'C0001', '2013-06-20', '1', 0, 1, 0, 3, 4)");	
	mysqli_query($connection, "insert into FoodParcel values (null, '2014-08-01', 'C0002', '2013-07-01', '0', 0, 0, 1, 3, 0)");	
	mysqli_query($connection, "insert into FoodParcel values (null, '2014-06-01', 'A0003', '2013-07-29', '0', 1, 0, 0, 1, 0)");	
	mysqli_query($connection, "insert into FoodParcel values (null, '2014-05-01', 'A0004', '2013-07-01', '0', 0, 1, 0, 1, 0)");	
	
	mysqli_query($connection, "insert into FPType_Contains values (2,1,1)"); 
	mysqli_query($connection, "insert into FPType_Contains values (1,1,2)");
	mysqli_query($connection, "insert into FPType_Contains values (1,1,3)");
	mysqli_query($connection, "insert into FPType_Contains values (1,1,4)");
	mysqli_query($connection, "insert into FPType_Contains values (2,2,1)"); 
	mysqli_query($connection, "insert into FPType_Contains values (1,2,2)");
	mysqli_query($connection, "insert into FPType_Contains values (1,2,6)");
	mysqli_query($connection, "insert into FPType_Contains values (1,2,5)");
	mysqli_query($connection, "insert into FPType_Contains values (2,3,1)"); 
	mysqli_query($connection, "insert into FPType_Contains values (2,3,4)");
	mysqli_query($connection, "insert into FPType_Contains values (1,3,7)");
	mysqli_query($connection, "insert into FPType_Contains values (3,3,6)");
	
	mysqli_query($connection, "insert into FoodParcelType values (null, 'yellow', 'A', 'Adult')");
	mysqli_query($connection, "insert into FoodParcelType values (null, 'blue', 'B', 'Second Adult')");
	mysqli_query($connection, "insert into FoodParcelType values (null, 'orange', 'C', 'Child')");
	
	mysqli_query($connection, "insert into Users values (null, 'hfl3', SHA1('senha'), 0 , 'Mr.', 'John', 'John', 'john@hotmail.com')");			
	mysqli_query($connection, "insert into Users values (null, 'volunteer1', '12345678', 0 , 'Mr.', 'John', 'Johnson', 'john@gmail.com')");
	mysqli_query($connection, "insert into Users values (null, 'volunteer2', '12345678', 1 , 'Mrs.', 'Debra', 'Smith', 'debra@gmail.com')");
	mysqli_query($connection, "insert into Users values (null, 'volunteer3', '12345678', 2 , 'Mr.', 'Michael', 'Jordan', 'jordan@gmail.com')");
	mysqli_query($connection, "insert into Users values (null, 'volunteer4', '12345678', 3 , 'Mrs.', 'Maria', 'Crawford', 'maria@gmail.com')");
	mysqli_query($connection, "insert into Users values (null, 'volunteer5', '12345678', 4 , 'Mr.', 'Peter', 'Barros', 'peter@gmail.com')");
	mysqli_query($connection, "insert into Users values (null, 'volunteer6', '12345678', 5 , 'Mrs.', 'Yui', 'Johnson', 'yui@gmail.com')");
	mysqli_query($connection, "insert into Users values (null, 'volunteer7', '12345678', 6 , 'Mr.', 'Henrique', 'Figueiroa', 'ique@gmail.com')");
	mysqli_query($connection, "insert into Users values (null, 'volunteer8', '12345678', 7 , 'Mrs.', 'Crystal', 'Santos', 'stal@gmail.com')");
	mysqli_query($connection, "insert into Users values (null, 'volunteer9', '12345678', 8 , 'Mr.', 'Marcos', 'Falcao', 'arcos@gmail.com')");
	mysqli_query($connection, "insert into Users values (null, 'volunteer10', '12345678', 9 , 'Mrs.', 'Marianne', 'Alexandrino', 'mca@gmail.com')");

	mysqli_query($connection, "insert into Volunteers values (null, 'volunteer1', '12345678', 0 , 'Mr.', 'John', 'Johnson', 'john@gmail.com', '09876876890', '07657456234', 'availability', 'roles', '5C Parkwood', 'UKC', 'CT27NX', 'Canterbury')");
	mysqli_query($connection, "insert into Volunteers values (null, 'volunteer2', '12345678', 1 , 'Mrs.', 'Ana', 'Key', 'key@gmail.com', '09876876890', '07657456234', 'availability', 'roles', '5C Parkwood', 'UKC', 'CT27NX', 'Whistable')");
	mysqli_query($connection, "insert into Volunteers values (null, 'volunteer3', '12345678', 2 , 'Mr.', 'Peter', 'Gabriel', 'gabriel@gmail.com', '09876876890', '07657456234', 'availability', 'roles', '5C Parkwood', 'UKC', 'CT27NX', 'Herne Bay')");
	mysqli_query($connection, "insert into Volunteers values (null, 'volunteer4', '12345678', 3 , 'Mrs.', 'Amy', 'Winehouse', 'amy@gmail.com', '09876876890', '07657456234', 'availability', 'roles', '5C Parkwood', 'UKC', 'CT27NX', 'Canterbury')");
	mysqli_query($connection, "insert into Volunteers values (null, 'volunteer5', '12345678', 4 , 'Mr.', 'Anthony', 'Kiedes', 'tony@gmail.com', '09876876890', '07657456234', 'availability', 'roles', '5C Parkwood', 'UKC', 'CT27NX', 'Whistable')");
	mysqli_query($connection, "insert into Volunteers values (null, 'volunteer6', '12345678', 5 , 'Mrs.', 'Clarice', 'Falcao', 'clarice@gmail.com', '09876876890', '07657456234', 'availability', 'roles', '5C Parkwood', 'UKC', 'CT27NX', 'Herne Bay')");
	mysqli_query($connection, "insert into Volunteers values (null, 'volunteer7', '12345678', 6 , 'Mr.', 'Dave', 'Ghrol', 'dave@gmail.com', '09876876890', '07657456234', 'availability', 'roles', '5C Parkwood', 'UKC', 'CT27NX', 'Canterbury')");
	mysqli_query($connection, "insert into Volunteers values (null, 'volunteer8', '12345678', 7 , 'Mrs.', 'Avril', 'Lavigne', 'avril@gmail.com', '09876876890', '07657456234', 'availability', 'roles', '5C Parkwood', 'UKC', 'CT27NX', 'Herne Bay')");
	mysqli_query($connection, "insert into Volunteers values (null, 'volunteer9', '12345678', 8 , 'Mr.', 'John', 'Frusciante', 'frusciante@gmail.com', '09876876890', '07657456234', 'availability', 'roles', '5C Parkwood', 'UKC', 'CT27NX', 'Whistable')");
	mysqli_query($connection, "insert into Volunteers values (null, 'volunteer10', '12345678', 9 , 'Mrs.', 'Adele', 'Blabla', 'adele@gmail.com', '09876876890', '07657456234', 'availability', 'roles', '5C Parkwood', 'UKC', 'CT27NX', 'Canterbury')");
	
	mysqli_query($connection, "insert into Work_warehouse values (1,1)"); //volunterrs and warehouse	
	mysqli_query($connection, "insert into Work_warehouse values (2,2)");
	mysqli_query($connection, "insert into Work_warehouse values (3,3)");
	mysqli_query($connection, "insert into Work_warehouse values (4,1)");
	mysqli_query($connection, "insert into Work_warehouse values (5,2)");
	mysqli_query($connection, "insert into Work_warehouse values (6,4)");
	mysqli_query($connection, "insert into Work_warehouse values (7,4)");
	
	mysqli_query($connection, "insert into Work_agency values (1,1)");	
	mysqli_query($connection, "insert into Work_agency values (4,2)");
	mysqli_query($connection, "insert into Work_agency values (2,3)");
	mysqli_query($connection, "insert into Work_agency values (3,1)");
	mysqli_query($connection, "insert into Work_agency values (4,1)");
	mysqli_query($connection, "insert into Work_agency values (4,2)");
	mysqli_query($connection, "insert into Work_agency values (4,3)");
	mysqli_query($connection, "insert into Work_agency values (5,1)");
	mysqli_query($connection, "insert into Work_agency values (6,2)");
	mysqli_query($connection, "insert into Work_agency values (7,3)");

	mysqli_query($connection, "insert into Work_dp values (1,1)"); 
	mysqli_query($connection, "insert into Work_dp values (2,2)");
	mysqli_query($connection, "insert into Work_dp values (3,3)");
	mysqli_query($connection, "insert into Work_dp values (4,1)");
	mysqli_query($connection, "insert into Work_dp values (1,2)");
	mysqli_query($connection, "insert into Work_dp values (5,3)");
	mysqli_query($connection, "insert into Work_dp values (6,1)");
	mysqli_query($connection, "insert into Work_dp values (9,2)");
	
	mysqli_query($connection, "insert into ReportedProblems values (null, '2013-08-05 10:20:30', 'voucher is not working', 1)");	
	mysqli_query($connection, "insert into ReportedProblems values (null, '2013-07-02 14:50:21', 'could not login', 2)");	
	mysqli_query($connection, "insert into ReportedProblems values (null, '2013-06-01 21:30:59', 'my permissions are limited', 3)");	
	
	mysqli_query($connection, "insert into Exchange values (null, 1, 'cw', '2013-07-11', 1)");
	mysqli_query($connection, "insert into Exchange values (null, 2, 'dp', '2013-05-31', 2)");
	mysqli_query($connection, "insert into Exchange values (null, 3, 'agency', '2013-07-31', 3)");
	mysqli_query($connection, "insert into Exchange values (null, 1, 'dp', '2013-04-30', 4)");

	mysqli_close($connection);
?>