# IPFS and Blockchain Based decentralized FIR lodging system

## Problem Statement

Suppose a person have an FIR lodged against him. So, he would face some demerits like: unable to travel countries and apply for a visa, unable to stand in elections or unable to apply for some jobs. 
So, people who are highly influential, try to modify or delete the physical records of the FIRs by bribing the low tier people. This issue can be solved using the emerging technoligies like Blockchain. A secured solution to this problem will help reduce the percentage of corruption in the country.

## Solution Approach

In the developed system, users will file an online FIR on the portal. 
A PDF version of the FIR will be sent to the nearest police station by email where the policeman will verify if the FIR is valid and give his affirmation by selecting and uploading the received PDF file.
Internally the PDF will be stored on the distributed ipfs system and later the obtained ipfs hash will be stored on blockchain.
As anyone having the ipfs hash can publicaly see the FIR lodged, this will help make the system more transparent.
Thus we implemented two layers of security viz distributed file on different peers of ipfs and storing the ipfs i.e address of FIR, on blockchain.

## Some Loopholes and posible solution

1. If the person verifying FIR is corrupt: We can implement a machine learning based fraud detection system to detect malcious behaviour.

2. To implement anonymity for user: We can maintain two backend blockchains. First one will contain everything except the identity of user and will be given for verification and stored publicly. Second will contain identity of users with their FIR numbers.The second database will only be acessible to higher officials and other people will have to file RTI applicaton to see those.

## Dependencies

1. PHP's FPDF library to generate pdf of the FIR filed.

2. SMTP library to send an email to the registered police station.

3. CodeIgniter Framework for the web part of the Application.

4. Rinkeby Test Network, ReactJS for the Blockchain uploading Part

![screenshot](Screenshot%20(200).png)
<br>
![screenshot](Screenshot%20(201).png)
