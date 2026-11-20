# Operator Payout Schedules

## A simple guide for MPOs and Operators

This guide explains when eligible bookings are grouped into Operator payouts for the four payout schedules: **Monthly**, **Quarterly**, **Service Provided**, and **On Request**.

## First: when does a booking become eligible?

A payout schedule does not make a booking eligible by itself. For an **Operator payout**, the booking must first pass these checks:

1. Payment is complete.
2. The service has taken place and its service date is at least **14 days ago** (the current configured delay).
3. The booking is in **USD** and has a positive gross amount.
4. For Transport, the booking status is **Completed**. Accommodation and Activity bookings must not be Pending or Cancelled.
5. An active Operator Collaboration Agreement must apply to the service date and have a supported percentage commission.
6. The Operator payout schedule must be active and supported.
7. The Operator commission plus the HIO SLA must not exceed the booking gross.

The service date used is:

| Service | Service date used |
|---|---|
| Accommodation | Check-out date |
| Activity | Activity date |
| Transport | Return date, or pickup date if there is no return date |

The system calculates:

> **Eligible date = service date + 14 days**

The examples below assume the booking passes all the other checks.

The separate **HIO SLA settlement** is not an Operator payout schedule. It is generated monthly for eligible USD bookings with a positive gross amount, and does not require the Operator payout schedule or an applicable Operator commission agreement.

## How the four schedules work

| Operator schedule | Who starts payout generation? | When can it be generated? | Period shown |
|---|---|---|---|
| **Monthly** | MPO clicks **Generate eligible settlements** | After the eligible-date month has ended | The calendar month containing the eligible date |
| **Quarterly** | MPO clicks **Generate eligible settlements** | After the eligible-date quarter has ended | The calendar quarter containing the eligible date |
| **Service Provided** | MPO clicks **Generate eligible settlements** | As soon as the booking reaches its eligible date; it does not wait for month-end | The eligible date |
| **On Request** | Operator clicks **Request eligible payout** | As soon as the booking reaches its eligible date; it does not wait for period-end | The calendar month containing the eligible date |

Generation is **not automatic in the background**. Monthly, Quarterly, and Service Provided payouts are considered when the MPO runs the generation action. On Request payouts are considered when the Operator submits a request.

## One example, four schedules

Suppose an Operator has a USD booking with:

- Service date: **October 20**
- Gross: **USD 100.00**
- Active agreement commission: **10%**
- HIO SLA: **0.5%**
- Eligible date: **November 3** (October 20 + 14 days)

If all other eligibility checks pass:

### Monthly

The eligible date is in **November**, so the booking belongs to the **November 1–30** period. The MPO can generate that monthly payout only **after November 30**. If the MPO runs generation on November 20, this booking is not in the November monthly payout yet because November is still open.

### Quarterly

The eligible date is in **Q4 (October–December)**, so the booking belongs to the **October 1–December 31** period. The MPO can generate that quarterly payout only **after December 31**.

### Service Provided

The booking reaches its eligible date on **November 3**. From then on, it can be included the next time the MPO clicks **Generate eligible settlements**. It does not have to wait until November 30. Its settlement period is the eligible date, **November 3**.

### On Request

From **November 3**, the booking can be included when the Operator clicks **Request eligible payout**. The MPO’s generation button does not generate On Request Operator payouts. The settlement period is **November 1–30**, the month containing the eligible date. A request can therefore be submitted in a later month while still showing the eligible-date month as its period.

## Diagram: booking to payout

```text
Service takes place
        |
        v
Service date + 14 days = eligible date
        |
        v
Payment, status, currency, agreement,
commission, and amount checks pass?
        |
        +---- No ----> No Operator payout; booking may be flagged for review
        |
        +---- Yes ---> Read the Operator's payout schedule
                          |
          +---------------+------------------+------------------+
          |               |                  |                  |
       Monthly         Quarterly       Service Provided      On Request
          |               |                  |                  |
     MPO generates   MPO generates     MPO generates       Operator requests
     after month     after quarter     once eligible       once eligible
     end             end                                  |
          +---------------+------------------+------------------+
                                          |
                                          v
                               Settlement grouped by schedule
                               and its applicable period
```

## How the amounts are calculated

Using the USD 100.00 example:

- Operator commission: **10% × 100 = USD 10.00**
- HIO SLA: **0.5% × 100 = USD 0.50**
- Net Operator payout: **100 − 10 − 0.50 = USD 89.50**

The HIO SLA is recorded as a separate HIO settlement. The Operator payout shows the Operator commission and the HIO SLA deduction used to calculate the Operator's net payable.

## If a booking does not appear

Check the booking's payment completion, service date and 14-day delay, currency and gross amount, booking status, active agreement and commission, and active Operator payout schedule. Also check whether the booking has already been included in a settlement of the same type; generation skips duplicate booking items.

Bookings that cannot be included may appear in **Bookings requiring authorised review**, with a reason such as missing agreement, unsupported commission, missing payout schedule, unsupported currency, or an invalid amount.

## Quick client summary

> Monthly and Quarterly payouts are grouped by the month or quarter in which a booking becomes eligible, and wait until that period closes. Service Provided payouts can be generated by the MPO as soon as the booking is eligible. On Request payouts can be requested by the Operator as soon as the booking is eligible. In every case, the booking must pass the payment, service, agreement, currency, and amount checks first.
