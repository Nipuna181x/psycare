# PsyCare System Test Plan

Result: ✅ Pass

## Patient Test Plan

| Test ID | Scenario |
|---|---|
| PT-01 | Check whether a patient can register, log in, and log out. |
| PT-02 | Check whether incorrect patient credentials are rejected. |
| PT-03 | Check whether a patient can book a doctor. |
| PT-04 | Check whether doctor filters work by location and date. |
| PT-05 | Check whether payment and Stripe Checkout work. |
| PT-06 | Check whether a patient can download a PDF receipt. |
| PT-07 | Check whether Lumi responds in English and Sinhala. |
| PT-08 | Check whether a patient can view previous health records. |
| PT-09 | Check whether a patient can view appointments and details. |
| PT-10 | Check whether assigned group therapy sessions are visible. |
| PT-11 | Check whether a patient can join an assigned group session. |
| PT-12 | Check whether a patient can record a daily mood. |
| PT-13 | Check whether Mood Tracker history is displayed. |
| PT-14 | Check whether pre-assessment works in English and Sinhala. |

## Patient Test Cases

### PT-01
1. **Test case no:** PT-01
2. **Scenario:** Check whether a patient can register, log in, and log out.
3. **Required result:** Account is created; login succeeds; logout ends the session.
4. **Obtained result:** The system created the account, logged in the patient, and ended the session on logout. ✅ Pass

### PT-02
1. **Test case no:** PT-02
2. **Scenario:** Check whether incorrect patient credentials are rejected.
3. **Required result:** Login is rejected with an error message.
4. **Obtained result:** The system rejected the credentials and displayed an error message. ✅ Pass

### PT-03
1. **Test case no:** PT-03
2. **Scenario:** Check whether a patient can book a doctor.
3. **Required result:** Booking reaches checkout with the correct details.
4. **Obtained result:** The system accepted the doctor, clinic, date, and time and opened checkout with correct details. ✅ Pass

### PT-04
1. **Test case no:** PT-04
2. **Scenario:** Check whether doctor filters work by location and date.
3. **Required result:** Only matching doctors are displayed.
4. **Obtained result:** The system displayed only doctors matching the selected location and date. ✅ Pass

### PT-05
1. **Test case no:** PT-05
2. **Scenario:** Check whether payment and Stripe Checkout work.
3. **Required result:** Payment succeeds and the appointment is confirmed.
4. **Obtained result:** Stripe accepted the payment and the system confirmed the appointment. ✅ Pass

### PT-06
1. **Test case no:** PT-06
2. **Scenario:** Check whether a patient can download a PDF receipt.
3. **Required result:** A PDF containing the payment ID and correct amounts downloads.
4. **Obtained result:** The system generated a PDF with the correct payment ID and amounts. ✅ Pass

### PT-07
1. **Test case no:** PT-07
2. **Scenario:** Check whether Lumi responds in English and Sinhala.
3. **Required result:** Lumi returns relevant text and audio in the selected language.
4. **Obtained result:** Lumi returned relevant text and audio in both selected languages. ✅ Pass

### PT-08
1. **Test case no:** PT-08
2. **Scenario:** Check whether a patient can view previous health records.
3. **Required result:** Previous permitted health records are displayed.
4. **Obtained result:** The system displayed the patient's permitted previous health records. ✅ Pass

### PT-09
1. **Test case no:** PT-09
2. **Scenario:** Check whether a patient can view appointments and details.
3. **Required result:** All owned appointments and correct details are shown.
4. **Obtained result:** The system displayed all owned appointments and their correct details. ✅ Pass

### PT-10
1. **Test case no:** PT-10
2. **Scenario:** Check whether assigned group therapy sessions are visible.
3. **Required result:** Assigned upcoming and past sessions are displayed.
4. **Obtained result:** The system displayed the patient's assigned upcoming and past sessions. ✅ Pass

### PT-11
1. **Test case no:** PT-11
2. **Scenario:** Check whether a patient can join an assigned group session.
3. **Required result:** The patient enters using an anonymous group alias.
4. **Obtained result:** The patient entered the live session using an anonymous alias. ✅ Pass

### PT-12
1. **Test case no:** PT-12
2. **Scenario:** Check whether a patient can record a daily mood.
3. **Required result:** One daily mood entry is saved or updated.
4. **Obtained result:** The system saved today's mood and updated it without creating a duplicate. ✅ Pass

### PT-13
1. **Test case no:** PT-13
2. **Scenario:** Check whether Mood Tracker history is displayed.
3. **Required result:** Previous entries and the mood trend are displayed newest first.
4. **Obtained result:** The system displayed previous entries and the mood trend newest first. ✅ Pass

### PT-14
1. **Test case no:** PT-14
2. **Scenario:** Check whether pre-assessment works in English and Sinhala.
3. **Required result:** Questions, answers, scores, and summary work in both languages.
4. **Obtained result:** The system processed questions and answers and produced scores and summaries in both languages. ✅ Pass

## Doctor Test Plan

| Test ID | Scenario |
|---|---|
| DR-01 | Check whether a doctor can register and log in. |
| DR-02 | Check whether an incorrect doctor password is rejected. |
| DR-03 | Check whether a doctor can view the dashboard. |
| DR-04 | Check whether a doctor can switch clinics. |
| DR-05 | Check whether a doctor can review the Crisis Queue. |
| DR-06 | Check whether a doctor receives and responds to clinic requests. |
| DR-07 | Check whether a doctor can view appointments and details. |
| DR-08 | Check whether a doctor can view all earnings. |
| DR-09 | Check whether a doctor can receive and confirm payouts. |
| DR-10 | Check whether a doctor can view patients and full profiles. |
| DR-11 | Check whether a doctor can host a group session. |
| DR-12 | Check whether a doctor receives notifications. |
| DR-13 | Check whether doctor profile settings can be updated. |
| DR-14 | Check whether all doctor portal filters work. |
| DR-15 | Check whether a doctor can cancel and complete appointments. |
| DR-16 | Check whether a doctor can download patient history as PDF. |

## Doctor Test Cases

### DR-01
1. **Test case no:** DR-01
2. **Scenario:** Check whether a doctor can register and log in.
3. **Required result:** Registration succeeds and an approved doctor reaches the portal.
4. **Obtained result:** The system registered the doctor and allowed the approved account to enter the portal. ✅ Pass

### DR-02
1. **Test case no:** DR-02
2. **Scenario:** Check whether an incorrect doctor password is rejected.
3. **Required result:** Login is rejected with an error message.
4. **Obtained result:** The system rejected the incorrect password and displayed an error message. ✅ Pass

### DR-03
1. **Test case no:** DR-03
2. **Scenario:** Check whether a doctor can view the dashboard.
3. **Required result:** Summary cards, appointments, risk data, and links load.
4. **Obtained result:** The dashboard loaded its summary cards, appointments, risk data, and links. ✅ Pass

### DR-04
1. **Test case no:** DR-04
2. **Scenario:** Check whether a doctor can switch clinics.
3. **Required result:** Clinic-scoped portal data changes to the selected clinic.
4. **Obtained result:** The system changed the active clinic and displayed its scoped data. ✅ Pass

### DR-05
1. **Test case no:** DR-05
2. **Scenario:** Check whether a doctor can review the Crisis Queue.
3. **Required result:** Urgent cases appear first; acknowledged cases move to Reviewed.
4. **Obtained result:** Urgent cases appeared first and the acknowledged case moved to Reviewed. ✅ Pass

### DR-06
1. **Test case no:** DR-06
2. **Scenario:** Check whether a doctor receives and responds to clinic requests.
3. **Required result:** Request status and clinic affiliation update correctly.
4. **Obtained result:** The system displayed the request and correctly updated the response and affiliation. ✅ Pass

### DR-07
1. **Test case no:** DR-07
2. **Scenario:** Check whether a doctor can view appointments and details.
3. **Required result:** Correct clinic appointments and clinical details are shown.
4. **Obtained result:** The system displayed the correct clinic appointments and clinical details. ✅ Pass

### DR-08
1. **Test case no:** DR-08
2. **Scenario:** Check whether a doctor can view all earnings.
3. **Required result:** Correct total and clinic earnings are displayed.
4. **Obtained result:** The system displayed correct total earnings and clinic breakdowns. ✅ Pass

### DR-09
1. **Test case no:** DR-09
2. **Scenario:** Check whether a doctor can receive and confirm payouts.
3. **Required result:** Payout moves from paid to completed.
4. **Obtained result:** The doctor confirmed receipt and the payout status changed to completed. ✅ Pass

### DR-10
1. **Test case no:** DR-10
2. **Scenario:** Check whether a doctor can view patients and full profiles.
3. **Required result:** Permitted profile, visits, medication, mood, and records are shown.
4. **Obtained result:** The system displayed permitted profile, visit, medication, mood, and record data. ✅ Pass

### DR-11
1. **Test case no:** DR-11
2. **Scenario:** Check whether a doctor can host a group session.
3. **Required result:** Assigned patients join anonymously and status changes correctly.
4. **Obtained result:** The doctor created, started, hosted, and ended the anonymous session successfully. ✅ Pass

### DR-12
1. **Test case no:** DR-12
2. **Scenario:** Check whether a doctor receives notifications.
3. **Required result:** Badge updates and the notification opens the correct page.
4. **Obtained result:** The notification appeared with a badge and opened the correct page. ✅ Pass

### DR-13
1. **Test case no:** DR-13
2. **Scenario:** Check whether doctor profile settings can be updated.
3. **Required result:** Valid changes persist and feedback is shown.
4. **Obtained result:** The system saved all valid changes and displayed success feedback. ✅ Pass

### DR-14
1. **Test case no:** DR-14
2. **Scenario:** Check whether all doctor portal filters work.
3. **Required result:** Lists show only matching records and reset correctly.
4. **Obtained result:** Each filter displayed matching records and reset correctly. ✅ Pass

### DR-15
1. **Test case no:** DR-15
2. **Scenario:** Check whether a doctor can cancel and complete appointments.
3. **Required result:** Each status updates without changing unrelated appointments.
4. **Obtained result:** The system updated both statuses without changing unrelated appointments. ✅ Pass

### DR-16
1. **Test case no:** DR-16
2. **Scenario:** Check whether a doctor can download patient history as PDF.
3. **Required result:** An authorized PDF report downloads with correct data.
4. **Obtained result:** The system authorized and downloaded a PDF containing the correct patient history. ✅ Pass

## Clinic Test Plan

| Test ID | Scenario |
|---|---|
| CL-01 | Check whether a clinic can register, log in, and log out. |
| CL-02 | Check whether an incorrect clinic password is rejected. |
| CL-03 | Check whether a clinic can find doctors by licence number or name. |
| CL-04 | Check whether a clinic can view patients and details. |
| CL-05 | Check whether a clinic can view revenue and analytics. |
| CL-06 | Check whether a clinic can record doctor payouts and view payments. |
| CL-07 | Check whether clinic profile settings can be updated. |
| CL-08 | Check whether a clinic can manage staff. |
| CL-09 | Check whether all clinic portal filters work. |

## Clinic Test Cases

### CL-01
1. **Test case no:** CL-01
2. **Scenario:** Check whether a clinic can register, log in, and log out.
3. **Required result:** Registration succeeds; approved clinic login and logout work.
4. **Obtained result:** The system registered the clinic, allowed approved login, and ended the session on logout. ✅ Pass

### CL-02
1. **Test case no:** CL-02
2. **Scenario:** Check whether an incorrect clinic password is rejected.
3. **Required result:** Login is rejected with an error message.
4. **Obtained result:** The system rejected the incorrect password and displayed an error message. ✅ Pass

### CL-03
1. **Test case no:** CL-03
2. **Scenario:** Check whether a clinic can find doctors by licence number or name.
3. **Required result:** Matching doctors and request status are displayed.
4. **Obtained result:** The system displayed matching doctors and their request statuses. ✅ Pass

### CL-04
1. **Test case no:** CL-04
2. **Scenario:** Check whether a clinic can view patients and details.
3. **Required result:** Only clinic-authorized patients and details are shown.
4. **Obtained result:** The system displayed only clinic-authorized patients and their details. ✅ Pass

### CL-05
1. **Test case no:** CL-05
2. **Scenario:** Check whether a clinic can view revenue and analytics.
3. **Required result:** Correct clinic totals, trends, and breakdowns are displayed.
4. **Obtained result:** The system displayed correct clinic totals, trends, and breakdowns. ✅ Pass

### CL-06
1. **Test case no:** CL-06
2. **Scenario:** Check whether a clinic can record doctor payouts and view payments.
3. **Required result:** Ledger status updates and remains visible to the doctor.
4. **Obtained result:** The system updated the payout ledger and showed the new status to the doctor. ✅ Pass

### CL-07
1. **Test case no:** CL-07
2. **Scenario:** Check whether clinic profile settings can be updated.
3. **Required result:** Valid values persist and booking slots use the new hours.
4. **Obtained result:** The system saved valid details and used the updated opening hours for booking slots. ✅ Pass

### CL-08
1. **Test case no:** CL-08
2. **Scenario:** Check whether a clinic can manage staff.
3. **Required result:** Staff access follows the updated status and clinic scope.
4. **Obtained result:** The system added, disabled, and removed staff with correct clinic access. ✅ Pass

### CL-09
1. **Test case no:** CL-09
2. **Scenario:** Check whether all clinic portal filters work.
3. **Required result:** Lists show only matching clinic records and reset correctly.
4. **Obtained result:** Each filter displayed matching clinic records and reset correctly. ✅ Pass

## Super Admin Test Plan

| Test ID | Scenario |
|---|---|
| AD-01 | Check whether a super admin can log in and log out. |
| AD-02 | Check whether incorrect super admin credentials are rejected. |
| AD-03 | Check whether a super admin can view all medical centers. |
| AD-04 | Check whether a super admin can approve or reject medical centers. |
| AD-05 | Check whether a super admin can view all doctors. |
| AD-06 | Check whether a super admin can approve or reject doctors. |
| AD-07 | Check whether a super admin can view and ban users. |
| AD-08 | Check whether the SMTP test sends an email. |
| AD-09 | Check whether all super admin filters work. |

## Super Admin Test Cases

### AD-01
1. **Test case no:** AD-01
2. **Scenario:** Check whether a super admin can log in and log out.
3. **Required result:** Dashboard opens and logout ends the session.
4. **Obtained result:** The dashboard opened with valid credentials and logout ended the session. ✅ Pass

### AD-02
1. **Test case no:** AD-02
2. **Scenario:** Check whether incorrect super admin credentials are rejected.
3. **Required result:** Login is rejected with an error message.
4. **Obtained result:** The system rejected the incorrect email or password and displayed an error. ✅ Pass

### AD-03
1. **Test case no:** AD-03
2. **Scenario:** Check whether a super admin can view all medical centers.
3. **Required result:** All registered medical centers and statuses are shown.
4. **Obtained result:** The system displayed all registered medical centers and their statuses. ✅ Pass

### AD-04
1. **Test case no:** AD-04
2. **Scenario:** Check whether a super admin can approve or reject medical centers.
3. **Required result:** Each clinic status and access update correctly.
4. **Obtained result:** The system correctly updated both clinic statuses and access. ✅ Pass

### AD-05
1. **Test case no:** AD-05
2. **Scenario:** Check whether a super admin can view all doctors.
3. **Required result:** All registered doctors and statuses are shown.
4. **Obtained result:** The system displayed all registered doctors and their statuses. ✅ Pass

### AD-06
1. **Test case no:** AD-06
2. **Scenario:** Check whether a super admin can approve or reject doctors.
3. **Required result:** Each doctor status and portal access update correctly.
4. **Obtained result:** The system correctly updated both doctor statuses and portal access. ✅ Pass

### AD-07
1. **Test case no:** AD-07
2. **Scenario:** Check whether a super admin can view and ban users.
3. **Required result:** User is marked banned and cannot access protected pages.
4. **Obtained result:** The system displayed users, banned the selected account, and blocked its access. ✅ Pass

### AD-08
1. **Test case no:** AD-08
2. **Scenario:** Check whether the SMTP test sends an email.
3. **Required result:** Email is accepted by SMTP and success feedback is shown.
4. **Obtained result:** SMTP accepted the message and the system displayed successful delivery feedback. ✅ Pass

### AD-09
1. **Test case no:** AD-09
2. **Scenario:** Check whether all super admin filters work.
3. **Required result:** Lists show only matching records and reset correctly.
4. **Obtained result:** Each filter displayed matching records and reset correctly. ✅ Pass

## Existing JSON Endpoint Test Plan

> PsyCare currently uses authenticated JSON web endpoints; no separate `routes/api.php` routes are registered.

| Test ID | Scenario |
|---|---|
| API-01 | Check whether `GET /booking/{doctor}/slots` returns booking slots. |
| API-02 | Check whether `POST /booking/{doctor}/assessment/interpret` processes an answer. |
| API-03 | Check whether `POST /ai-companion/start` starts English and Sinhala sessions. |
| API-04 | Check whether `POST /ai-companion/respond` returns Lumi's response. |
| API-05 | Check whether `POST /ai-companion/finish` ends a Lumi session. |
| API-06 | Check whether an assigned patient can send a live-room signal. |
| API-07 | Check whether the host doctor can retrieve the room roster. |
| API-08 | Check whether the host doctor can send a live-room signal. |

## Existing JSON Endpoint Test Cases

### API-01
1. **Test case no:** API-01
2. **Scenario:** Check whether `GET /booking/{doctor}/slots` returns booking slots.
3. **Required result:** Returns `200` and current available/disabled time slots.
4. **Obtained result:** The endpoint returned `200` with current available and disabled slots. ✅ Pass

### API-02
1. **Test case no:** API-02
2. **Scenario:** Check whether `POST /booking/{doctor}/assessment/interpret` processes an answer.
3. **Required result:** Returns `200` with score, confidence, and clarification status.
4. **Obtained result:** The endpoint returned `200` with score, confidence, and clarification status. ✅ Pass

### API-03
1. **Test case no:** API-03
2. **Scenario:** Check whether `POST /ai-companion/start` starts English and Sinhala sessions.
3. **Required result:** Returns `200`, session ID, greeting, audio, and audio type.
4. **Obtained result:** The endpoint returned `200` with session ID, greeting, audio, and audio type. ✅ Pass

### API-04
1. **Test case no:** API-04
2. **Scenario:** Check whether `POST /ai-companion/respond` returns Lumi's response.
3. **Required result:** Returns `200` with Lumi's text and audio response.
4. **Obtained result:** The endpoint returned `200` with Lumi's relevant text and audio response. ✅ Pass

### API-05
1. **Test case no:** API-05
2. **Scenario:** Check whether `POST /ai-companion/finish` ends a Lumi session.
3. **Required result:** Returns `200` with report ID/status or no-conversation status.
4. **Obtained result:** The endpoint returned `200` with the report status or no-conversation status. ✅ Pass

### API-06
1. **Test case no:** API-06
2. **Scenario:** Check whether an assigned patient can send a live-room signal.
3. **Required result:** Live-room signal is authorized and returns `{"sent":true}`.
4. **Obtained result:** The endpoint authorized the patient and returned `{"sent":true}`. ✅ Pass

### API-07
1. **Test case no:** API-07
2. **Scenario:** Check whether the host doctor can retrieve the room roster.
3. **Required result:** Returns `200` with the authorized participant roster.
4. **Obtained result:** The endpoint returned `200` with the authorized participant roster. ✅ Pass

### API-08
1. **Test case no:** API-08
2. **Scenario:** Check whether the host doctor can send a live-room signal.
3. **Required result:** Live-room signal is authorized and returns `{"sent":true}`.
4. **Obtained result:** The endpoint authorized the doctor and returned `{"sent":true}`. ✅ Pass
