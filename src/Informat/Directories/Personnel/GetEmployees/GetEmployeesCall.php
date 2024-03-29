<?php

namespace Koba\Informat\Directories\Personnel\GetEmployees;

use Koba\Informat\Call\AbstractCall;
use Koba\Informat\Call\HasQueryParamsInterface;
use Koba\Informat\Call\HasQueryParamsTrait;
use Koba\Informat\Directories\DirectoryInterface;
use Koba\Informat\Enums\HttpMethod;
use Koba\Informat\Helpers\JsonMapper;
use Koba\Informat\Helpers\Schoolyear;
use Koba\Informat\Responses\Personnel\Employee;

/**
 * Gets all the employees for the combination 
 * institute number, school year and structure.
 */
class GetEmployeesCall
extends AbstractCall
implements HasQueryParamsInterface
{
    use HasQueryParamsTrait;

    public static function make(
        DirectoryInterface $directory,
        string $instituteNumber,
        null|int|string $schoolyear,
    ): self {
        return (new self($directory, $instituteNumber))
            ->setSchoolyear($schoolyear);
    }

    protected function getMethod(): HttpMethod
    {
        return HttpMethod::GET;
    }

    protected function getEndpoint(): string
    {
        return 'employees';
    }

    protected function getApiVersion(): ?string
    {
        return '2';
    }

    /**
     * For current & future school years: It limits the output results
     * to employees with an assignment within the given schoolyear 
     * (and instituteNo) or which are marked as active for your instituteNo.
     * For passed school years: It limits the output results to employees 
     * with an assignment within the given schoolyear (and instituteNo). 
     * Also used to determine the instituteNo’s for the properties 
     * “EersteDienstScholengroep” and “EersteDienstScholengemeenschap”.
     */
    public function setSchoolyear(null|int|string $schoolyear): self
    {
        $this->setQueryParam('schoolYear', new Schoolyear($schoolyear));
        return $this;
    }

    /**
     * This is an actually an additional restriction on the school year filter.
     * Also used to determine the properties “HoofdAmbt”, “EersteDienstSchool”
     * and “IsActive” in a more detailed way, by means of the combination
     * instituteNo & structure.
     * 
     * Note: Only structures “311” & “312” are taken into account. 
     */
    public function setStructure(string $structure): self
    {
        $this->setQueryParam('structure', $structure);
        return $this;
    }

    /**
     * Perform the API call.
     * @return Employee[]
     */
    public function send(): array
    {
        return (new JsonMapper)->mapArray(
            $this->performRequest(),
            Employee::class
        );
    }
}
